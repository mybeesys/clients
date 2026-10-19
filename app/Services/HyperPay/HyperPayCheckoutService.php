<?php

namespace App\Services\HyperPay;

use App\Models\Company;
use App\Models\HyperPayPayment;
use App\Models\PaymentSubscription;
use App\Models\SavedPaymentCard;
use App\Models\User;
use App\Services\Entitlements\EntitlementProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class HyperPayCheckoutService
{
    public function __construct(
        protected HyperPayConfig $config,
        protected HyperPayGateway $gateway,
    ) {
    }

    public static function instance(): self
    {
        $config = HyperPayConfig::instance();

        return new self($config, HyperPayGateway::make($config));
    }

    /**
     * @param  array<string, mixed>  $planConfig
     * @param  array<string, mixed>  $quote
     * @param  array<string, string>  $billing
     */
    public function startRegistration(array $planConfig, array $quote, array $billing, string $sessionToken): HyperPayPayment
    {
        return $this->startCheckout($planConfig, $quote, $billing, [
            'session_token' => $sessionToken,
            'source' => HyperPayPayment::SOURCE_REGISTRATION,
        ]);
    }

    /**
     * @param  array<string, mixed>  $planConfig
     * @param  array<string, mixed>  $quote
     * @param  array<string, string>  $billing
     */
    public function startSubscription(
        Company $company,
        array $planConfig,
        array $quote,
        array $billing,
        string $intent,
        string $sessionToken,
    ): HyperPayPayment {
        $quote['checkout_intent'] = match ($intent) {
            'renew' => 'renew',
            'addon' => 'addon',
            default => 'replace',
        };

        return $this->startCheckout($planConfig, $quote, $billing, [
            'company_id' => $company->id,
            'session_token' => $sessionToken,
            'source' => HyperPayPayment::SOURCE_SUBSCRIPTION,
        ]);
    }

    /**
     * Vault a card via a small verification charge (createRegistration).
     *
     * @param  array<string, string>  $billing
     */
    public function startSaveCard(Company $company, array $billing, string $sessionToken): HyperPayPayment
    {
        $amount = $this->saveCardVerificationAmount();
        $currency = $this->config->currency();
        $label = __('fields.hyperpay_save_card_line');

        $quote = [
            'period' => 'Month',
            'period_subtotal' => $amount,
            'period_total' => $amount,
            'discount' => 0,
            'currency' => $currency,
            'checkout_intent' => 'save_card',
            'line_items' => [
                ['label' => $label, 'amount' => $amount],
            ],
        ];

        return $this->startCheckout([], $quote, $billing, [
            'company_id' => $company->id,
            'session_token' => $sessionToken,
            'source' => HyperPayPayment::SOURCE_SAVE_CARD,
        ]);
    }

    public function saveCardVerificationAmount(): float
    {
        return 1.0;
    }

    /**
     * @param  array<string, mixed>  $planConfig
     * @param  array<string, mixed>  $quote
     * @param  array<string, string>  $billing
     * @param  array<string, mixed>  $attributes
     */
    public function startCheckout(array $planConfig, array $quote, array $billing, array $attributes = []): HyperPayPayment
    {
        if (! $this->config->isConfigured()) {
            throw new RuntimeException(__('fields.hyperpay_not_configured'));
        }

        $this->assertBilling($billing);

        $amount = (float) ($quote['period_total'] ?? 0);

        if ($amount <= 0) {
            throw new InvalidArgumentException(__('fields.hyperpay_free_plan_no_checkout'));
        }

        $formattedAmount = $this->config->formatAmount($amount);
        $merchantTransactionId = $this->uniqueMerchantTransactionId();

        $payload = $this->checkoutPayload($formattedAmount, $merchantTransactionId, $billing);

        $payment = HyperPayPayment::query()->create([
            'session_token' => $attributes['session_token'] ?? null,
            'company_id' => $attributes['company_id'] ?? null,
            'config_hash' => HyperPayPayment::hashPlanConfig($planConfig),
            'billing_period' => ($quote['period'] ?? 'Month') === 'Year' ? 'Year' : 'Month',
            'coupon_code' => $quote['coupon_code'] ?? null,
            'amount' => (float) $formattedAmount,
            'currency' => $this->config->currency(),
            'merchant_transaction_id' => $merchantTransactionId,
            'status' => HyperPayPayment::STATUS_PENDING,
            'source' => $attributes['source'] ?? HyperPayPayment::SOURCE_REGISTRATION,
            'billing' => $billing,
            'plan_config' => $planConfig,
            'quote' => $quote,
            'request_payload' => $this->safePayload($payload),
        ]);

        try {
            $checkout = $this->gateway->createCheckout($payload);
        } catch (\Throwable $exception) {
            $payment->fill([
                'status' => HyperPayPayment::STATUS_FAILED,
                'failed_at' => now(),
                'result_description' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }

        $payment->fill([
            'checkout_id' => $checkout['id'] ?? null,
            'integrity' => $checkout['integrity'] ?? null,
            'status' => HyperPayPayment::STATUS_CHECKOUT,
            'gateway_response' => $checkout,
        ])->save();

        return $payment->fresh();
    }

    public function complete(HyperPayPayment $payment, string $resourcePath): HyperPayPayment
    {
        if ($payment->isPaid()) {
            return $payment;
        }

        if (! $payment->isOpen()) {
            throw new RuntimeException(__('fields.hyperpay_payment_closed'));
        }

        $status = $this->gateway->fetchPaymentStatus($resourcePath);
        $code = (string) data_get($status, 'result.code', '');
        $description = (string) data_get($status, 'result.description', '');

        $payment->fill([
            'resource_path' => $resourcePath,
            'payment_id' => $status['id'] ?? $payment->payment_id,
            'brand' => data_get($status, 'paymentBrand'),
            'result_code' => $code,
            'result_description' => $description,
            'gateway_response' => $status,
        ]);

        if (HyperPayResult::isPending($code)) {
            $payment->status = HyperPayPayment::STATUS_PENDING_GATEWAY;
            $payment->save();

            return $payment->fresh();
        }

        if (! HyperPayResult::isSuccessful($code)) {
            $payment->status = HyperPayPayment::STATUS_FAILED;
            $payment->failed_at = now();
            $payment->save();

            return $payment->fresh();
        }

        $paid = DB::transaction(function () use ($payment) {
            $locked = HyperPayPayment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($locked->isPaid()) {
                return $locked->fresh();
            }

            $locked->fill([
                'status' => HyperPayPayment::STATUS_PAID,
                'paid_at' => now(),
            ])->save();

            return $locked->fresh();
        });

        $this->rememberSavedCard($paid, $status);

        if ($paid->source === HyperPayPayment::SOURCE_SUBSCRIPTION) {
            try {
                return $this->fulfillSubscription($paid);
            } catch (\Throwable $exception) {
                report($exception);

                return $paid->fresh();
            }
        }

        return $paid;
    }

    /**
     * @param  array<string, mixed>  $gatewayResponse
     */
    protected function rememberSavedCard(HyperPayPayment $payment, array $gatewayResponse): void
    {
        if (! $payment->company_id) {
            return;
        }

        $company = Company::query()->find($payment->company_id);
        if (! $company) {
            return;
        }

        try {
            app(SavedPaymentCardService::class)->storeFromGatewayResponse(
                $company,
                $gatewayResponse,
                $payment,
                $company->user,
            );
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function fulfillSubscription(HyperPayPayment $payment): HyperPayPayment
    {
        if ($payment->source !== HyperPayPayment::SOURCE_SUBSCRIPTION || ! $payment->isPaid()) {
            return $payment;
        }

        if ($payment->subscription_id) {
            return $payment;
        }

        return DB::transaction(function () use ($payment) {
            $locked = HyperPayPayment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($locked->subscription_id) {
                return $locked->fresh();
            }

            $company = Company::query()->findOrFail($locked->company_id);
            $intent = (string) data_get($locked->quote, 'checkout_intent', 'replace');
            $planConfig = is_array($locked->plan_config) ? $locked->plan_config : [];
            $provisioner = app(EntitlementProvisioner::class);

            if ($intent === 'renew') {
                $entitlement = $provisioner->renew($company, [
                    'coupon_code' => $locked->coupon_code ?: ($planConfig['coupon_code'] ?? null),
                    'period' => ($planConfig['period'] ?? data_get($locked->quote, 'period')) === 'Month' ? 'Month' : 'Year',
                ]);
            } elseif ($intent === 'addon') {
                $entitlement = $provisioner->applyAddon($company, [
                    'employees' => (int) ($planConfig['employees'] ?? 0),
                    'establishments' => (int) ($planConfig['establishments'] ?? 0),
                    'screen_devices' => (int) ($planConfig['screen_devices'] ?? 0),
                    'pos_devices' => (int) ($planConfig['pos_devices'] ?? 0),
                    'waiter_seats' => (int) ($planConfig['waiter_seats'] ?? 0),
                    'kitchen_devices' => (int) ($planConfig['kitchen_devices'] ?? 0),
                ]);
            } else {
                $entitlement = $provisioner->replace($company, $planConfig);
            }

            $entitlement->loadMissing('subscription');
            $subscriptionId = $entitlement->subscription_id
                ?: $company->subscription?->id;

            $locked->forceFill([
                'plan_id' => $entitlement->subscription?->plan_id ?? $company->subscription?->plan_id,
                'subscription_id' => $subscriptionId,
            ])->save();

            if ($subscriptionId) {
                PaymentSubscription::query()->create([
                    'subscription_id' => $subscriptionId,
                    'company_id' => $company->id,
                    'plan_id' => $entitlement->subscription?->plan_id ?? $company->subscription?->plan_id,
                    'amount' => $locked->amount,
                    'payment_date' => now()->toDateString(),
                    'payment_method' => 'hyperpay',
                    'status' => 'paid',
                    'transaction_id' => $locked->payment_id ?: $locked->merchant_transaction_id,
                    'paid_amount' => $locked->amount,
                    'remaining_amount' => 0,
                ]);
            }

            return $locked->fresh();
        });
    }

    /**
     * @param  array<string, string>  $billing
     * @return array<string, mixed>
     */
    public function checkoutPayload(string $amount, string $merchantTransactionId, array $billing): array
    {
        $payload = [
            'entityId' => $this->config->entityId(),
            'amount' => $amount,
            'currency' => $this->config->currency(),
            'paymentType' => $this->config->paymentType(),
            'merchantTransactionId' => $merchantTransactionId,
            'customer.email' => $billing['email'],
            'customer.givenName' => $billing['given_name'],
            'customer.surname' => $billing['surname'],
            'billing.street1' => $billing['street1'],
            'billing.city' => $billing['city'],
            'billing.state' => $billing['state'],
            'billing.country' => strtoupper($billing['country']),
            'billing.postcode' => $billing['postcode'],
            'integrity' => 'true',
            // Save the card for later MIT renewals / one-click pay.
            'createRegistration' => 'true',
            'standingInstruction.mode' => 'INITIAL',
            'standingInstruction.type' => 'UNSCHEDULED',
            'standingInstruction.source' => 'CIT',
        ];

        if ($this->config->isTest()) {
            $payload['testMode'] = 'EXTERNAL';
            $payload['customParameters[3DS2_enrolled]'] = 'true';
            $payload['customParameters[3DS2_flow]'] = 'challenge';
        }

        return $payload;
    }

    /**
     * Charge the company's default (or given) saved card without the widget.
     *
     * @param  array<string, mixed>  $planConfig
     * @param  array<string, mixed>  $quote
     * @param  array<string, string>  $billing
     */
    public function chargeSavedCard(
        Company $company,
        array $planConfig,
        array $quote,
        array $billing,
        string $intent,
        ?SavedPaymentCard $card = null,
    ): HyperPayPayment {
        if (! $this->config->isConfigured()) {
            throw new RuntimeException(__('fields.hyperpay_not_configured'));
        }

        $card ??= app(SavedPaymentCardService::class)->defaultFor($company);
        if (! $card || ! $card->isActive() || blank($card->registration_id)) {
            throw new RuntimeException(__('fields.hyperpay_saved_card_missing'));
        }

        $this->assertBilling($billing);

        $amount = (float) ($quote['period_total'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException(__('fields.hyperpay_free_plan_no_checkout'));
        }

        $quote['checkout_intent'] = match ($intent) {
            'renew' => 'renew',
            'addon' => 'addon',
            default => 'replace',
        };

        $formattedAmount = $this->config->formatAmount($amount);
        $merchantTransactionId = $this->uniqueMerchantTransactionId();

        $payload = [
            'entityId' => $this->config->entityId(),
            'amount' => $formattedAmount,
            'currency' => $this->config->currency(),
            'paymentType' => $this->config->paymentType(),
            'merchantTransactionId' => $merchantTransactionId,
            'customer.email' => $billing['email'],
            'customer.givenName' => $billing['given_name'],
            'customer.surname' => $billing['surname'],
            'standingInstruction.mode' => 'REPEATED',
            'standingInstruction.type' => 'UNSCHEDULED',
            'standingInstruction.source' => 'MIT',
        ];

        if ($this->config->isTest()) {
            $payload['testMode'] = 'EXTERNAL';
        }

        $payment = HyperPayPayment::query()->create([
            'company_id' => $company->id,
            'config_hash' => HyperPayPayment::hashPlanConfig($planConfig),
            'billing_period' => ($quote['period'] ?? 'Month') === 'Year' ? 'Year' : 'Month',
            'coupon_code' => $quote['coupon_code'] ?? null,
            'amount' => (float) $formattedAmount,
            'currency' => $this->config->currency(),
            'merchant_transaction_id' => $merchantTransactionId,
            'status' => HyperPayPayment::STATUS_PENDING,
            'source' => HyperPayPayment::SOURCE_SUBSCRIPTION,
            'brand' => $card->brand,
            'billing' => $billing,
            'plan_config' => $planConfig,
            'quote' => $quote,
            'request_payload' => $this->safePayload($payload + ['registrationId' => $card->registration_id]),
        ]);

        try {
            $response = $this->gateway->chargeRegistration($card->registration_id, $payload);
        } catch (\Throwable $exception) {
            $payment->fill([
                'status' => HyperPayPayment::STATUS_FAILED,
                'failed_at' => now(),
                'result_description' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }

        $code = (string) data_get($response, 'result.code', '');
        $description = (string) data_get($response, 'result.description', '');

        $payment->fill([
            'payment_id' => $response['id'] ?? null,
            'brand' => data_get($response, 'paymentBrand', $card->brand),
            'result_code' => $code,
            'result_description' => $description,
            'gateway_response' => $response,
        ]);

        if (HyperPayResult::isPending($code)) {
            $payment->status = HyperPayPayment::STATUS_PENDING_GATEWAY;
            $payment->save();

            return $payment->fresh();
        }

        if (! HyperPayResult::isSuccessful($code)) {
            $payment->status = HyperPayPayment::STATUS_FAILED;
            $payment->failed_at = now();
            $payment->save();

            throw new RuntimeException($description !== '' ? $description : __('fields.hyperpay_payment_failed_body'));
        }

        $payment->fill([
            'status' => HyperPayPayment::STATUS_PAID,
            'paid_at' => now(),
        ])->save();

        $paid = $payment->fresh();

        try {
            return $this->fulfillSubscription($paid);
        } catch (\Throwable $exception) {
            report($exception);

            return $paid;
        }
    }

    /**
     * @return array<string, string>
     */
    public static function placeholderBilling(): array
    {
        return [
            'given_name' => 'MyBee',
            'surname' => 'Customer',
            'email' => 'checkout.'.strtolower(Str::random(10)).'@mybeesystem.com',
            'street1' => 'King Fahd Road',
            'city' => 'Riyadh',
            'state' => 'Riyadh',
            'country' => 'SA',
            'postcode' => '11564',
        ];
    }

    public static function billingFromCompany(Company $company, ?User $user = null): array
    {
        $company->loadMissing('country');
        $user ??= $company->user;
        $fallback = static::placeholderBilling();

        $rawName = trim((string) ($user?->name ?: $company->ceo_name ?: $company->name ?: ''));
        $parts = preg_split('/\s+/', $rawName, 2) ?: [];
        $given = (string) ($parts[0] ?? '');
        $surname = (string) ($parts[1] ?? $given);

        if ($given === '' || preg_match('/[^\x00-\x7F]/', $given.$surname)) {
            $given = $fallback['given_name'];
            $surname = $fallback['surname'];
        }

        $street = trim((string) ($company->national_address ?: ''));
        if ($street === '' || preg_match('/[^\x00-\x7F]/', $street)) {
            $street = $fallback['street1'];
        }

        $city = trim((string) ($company->city ?: $fallback['city']));
        if ($city === '' || preg_match('/[^\x00-\x7F]/', $city)) {
            $city = $fallback['city'];
        }

        $state = trim((string) ($company->state ?: $city));
        if ($state === '' || preg_match('/[^\x00-\x7F]/', $state)) {
            $state = $city;
        }

        $iso = strtoupper((string) ($company->country?->iso_code ?? 'SA'));
        if (! preg_match('/^[A-Z]{2}$/', $iso)) {
            $iso = 'SA';
        }

        $postcode = preg_replace('/\D+/', '', (string) ($company->zipcode ?: '')) ?: '';
        if ($iso === 'SA' && ! preg_match('/^\d{5}$/', $postcode)) {
            $postcode = $fallback['postcode'];
        } elseif ($postcode === '') {
            $postcode = $fallback['postcode'];
        }

        $email = trim((string) ($user?->email ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = 'billing+'.$company->id.'@mybeesystem.com';
        }

        return [
            'given_name' => mb_substr($given, 0, 40),
            'surname' => mb_substr($surname, 0, 40),
            'email' => $email,
            'street1' => mb_substr($street, 0, 80),
            'city' => mb_substr($city, 0, 40),
            'state' => mb_substr($state, 0, 40),
            'country' => $iso,
            'postcode' => $postcode,
        ];
    }

    /**
     * @param  array<string, string>  $billing
     */
    public function assertBilling(array $billing): void
    {
        $required = ['given_name', 'surname', 'email', 'street1', 'city', 'state', 'country', 'postcode'];

        foreach ($required as $field) {
            if (! filled($billing[$field] ?? null)) {
                throw new InvalidArgumentException($this->trans('fields.hyperpay_billing_incomplete', 'Complete all billing fields.'));
            }
        }

        if (! preg_match('/^[A-Z]{2}$/', strtoupper((string) $billing['country']))) {
            throw new InvalidArgumentException($this->trans('fields.hyperpay_billing_country_invalid', 'Country must be a 2-letter ISO code.'));
        }

        $country = strtoupper((string) $billing['country']);
        $postcode = trim((string) $billing['postcode']);

        if ($country === 'SA' && ! preg_match('/^\d{5}$/', $postcode)) {
            throw new InvalidArgumentException($this->trans('fields.hyperpay_billing_postcode_invalid', 'Saudi postal code must be 5 digits.'));
        }

        if ($country !== 'SA' && ! preg_match('/^[A-Za-z0-9][A-Za-z0-9 \-]{2,11}$/', $postcode)) {
            throw new InvalidArgumentException($this->trans('fields.hyperpay_billing_postcode_invalid', 'Postal code format is invalid.'));
        }
    }

    public function widgetScriptUrl(HyperPayPayment $payment): string
    {
        return $this->config->baseUrl().'/v1/paymentWidgets.js?checkoutId='.urlencode((string) $payment->checkout_id);
    }

    protected function trans(string $key, string $fallback): string
    {
        try {
            if (function_exists('app') && app()->bound('translator')) {
                return (string) __($key);
            }
        } catch (\Throwable) {
        }

        return $fallback;
    }

    protected function uniqueMerchantTransactionId(): string
    {
        do {
            $id = 'CL-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        } while (HyperPayPayment::query()->where('merchant_transaction_id', $id)->exists());

        return $id;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function safePayload(array $payload): array
    {
        unset($payload['entityId']);

        return $payload;
    }
}
