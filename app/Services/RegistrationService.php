<?php

namespace App\Services;

use App\Mail\CompanyReadyMail;
use App\Models\Company;
use App\Models\Country;
use App\Models\HyperPayPayment;
use App\Models\PaymentSubscription;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Entitlements\EntitlementPricer;
use App\Services\RegistrationEmailVerifier;
use App\Services\Entitlements\EntitlementProvisioner;
use App\Services\ReferralService;
use App\Support\TenantApplicationUrl;
use App\Support\TenantKeyGenerator;
use App\Support\TenantProvisioner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class RegistrationService
{
    public function register(array $data): User
    {
        if (! array_key_exists('password_confirmation', $data) && array_key_exists('passwordConfirmation', $data)) {
            $data['password_confirmation'] = $data['passwordConfirmation'];
        }

        Validator::make($data, [
            'userName' => ['required', 'string', 'min:2', 'max:255', Rule::unique('users', 'name')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['required', 'string', 'regex:/^\+9665[0-9]{8}$/'],
            'password' => ['required', 'string', 'max:255', 'confirmed'],
        ], [
            'phone_number.regex' => __('main.wizard.saudi_phone_invalid'),
        ], [
            'userName' => __('fields.name'),
            'email' => __('fields.email'),
            'phone_number' => __('fields.phone_number'),
            'password' => __('fields.password'),
        ])->validate();

        app(RegistrationEmailVerifier::class)->assertVerified((string) $data['email']);

        $payload = $this->mapCompanyPayload($data);
        $payment = $this->requirePaidPlanIfNeeded($data);

        $user = User::create([
            'name' => $data['userName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_company' => true,
            'email_verified_at' => now(),
        ]);

        try {
            $company = (new CompanyAction($user))->storeCompany($payload);

            $this->createSubscription($company, $data);
            $this->attachCardPayment($company, $payment);

            app(ReferralService::class)->handleRegistrationConversion(
                referralCodeValue: $data['referral_code'] ?? session('referral_code'),
                company: $company,
                subscriber: $user,
                planId: $company->fresh()?->subscription?->plan_id,
                request: request(),
            );

            $tenantId = (string) ($company->tenant?->getKey() ?? '');
            $this->queueWelcomeEmail($user, $company, (string) $data['password']);
            TenantProvisioner::start($tenantId);

            return $user;
        } catch (Throwable $e) {
            $this->rollback($user);

            throw $e;
        }
    }

    /**
     * The mail is delivered after the tenant database is provisioned, which
     * itself runs after the registration response is sent.
     */
    protected function queueWelcomeEmail(User $user, Company $company, string $password): void
    {
        $tenantId = (string) ($company->tenant?->getKey() ?? '');

        if ($tenantId === '') {
            return;
        }

        Cache::put(self::welcomeCacheKey($tenantId), [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'password' => $password,
        ], now()->addMinutes(30));
    }

    public function deliverQueuedWelcomeEmail(?string $tenantId = null): void
    {
        $payload = is_string($tenantId) && $tenantId !== ''
            ? Cache::pull(self::welcomeCacheKey($tenantId))
            : null;

        if (! is_array($payload)) {
            return;
        }

        $user = User::query()->find($payload['user_id'] ?? null);
        $company = Company::query()->find($payload['company_id'] ?? null);

        if (! $user || ! $company || ! is_string($payload['password'] ?? null)) {
            return;
        }

        $this->sendWelcomeEmail($user, $company, $payload['password']);
    }

    public static function welcomeCacheKey(string $tenantId): string
    {
        return 'registration.welcome.'.$tenantId;
    }

    protected function sendWelcomeEmail(User $user, Company $company, string $password): void
    {
        try {
            $user->unsetRelation('company');
            $user->load(['company.tenant.domains', 'tenant.domains']);

            Mail::to($user->email)->send(new CompanyReadyMail(
                ownerName: $user->name,
                companyName: (string) $company->name,
                tenantUrl: TenantApplicationUrl::forUser($user),
                username: $user->email,
                password: $password,
            ));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    protected function mapCompanyPayload(array $data): array
    {
        $company = $data['company'] ?? [];

        $tenantKey = $company['tenant_key']
            ?? TenantKeyGenerator::fromEnglishName($company['name_en'] ?? null);

        if (Tenant::query()->whereKey($tenantKey)->exists()) {
            throw ValidationException::withMessages([
                'company.name_en' => __('main.wizard.tenant_key_taken'),
            ]);
        }

        return [
            'name' => $company['name_ar'],
            'name_en' => $company['name_en'] ?? null,
            'tenant_key' => $tenantKey,
            'city' => $company['city'] ?? '-',
            'tax_number' => $company['tax_number'] ?? null,
            'description' => $company['description'] ?? null,
            'business_type' => $company['business_type'] ?? 'general',
            'country_id' => $company['country_id'] ?? Country::defaultId(),
            'state' => $company['state'] ?? '-',
            'zipcode' => $company['zipcode'] ?? '00000',
            'national_address' => $company['national_address'] ?? null,
            'phone' => $company['phone'] ?? ($data['phone_number'] ?? null),
            'website' => $company['website'] ?? null,
            'ceo_name' => $company['ceo_name'] ?? null,
            'tax_name' => $company['tax_name'] ?? null,
            'logo' => $company['logo'] ?? null,
            'owner_phone_number' => $data['phone_number'] ?? null,
        ];
    }

    protected function createSubscription(Company $company, array $data): void
    {
        $planConfig = $data['plan_config'] ?? null;

        if (is_array($planConfig) && ! empty($planConfig['modules'])) {
            app(EntitlementProvisioner::class)->provision($company, $planConfig);

            return;
        }

        $subscription = $data['subscription'] ?? null;

        if (empty($subscription['plan_id'])) {
            return;
        }

        Subscription::create([
            'plan_id' => $subscription['plan_id'],
            'started_at' => $subscription['started_at'] ?? now(),
            'expired_at' => $subscription['expired_at'] ?? null,
            'grace_days_ended_at' => $subscription['grace_days_ended_at'] ?? null,
            'subscriber_type' => Company::class,
            'subscriber_id' => $company->id,
        ]);
    }

    protected function rollback(User $user): void
    {
        $company = Company::query()->where('user_id', $user->id)->first();

        if ($company) {
            try {
                $company->tenant?->delete();
            } catch (Throwable) {
            }

            $company->forceDelete();
        }

        $user->forceDelete();
    }

    protected function requirePaidPlanIfNeeded(array $data): ?HyperPayPayment
    {
        $planConfig = $data['plan_config'] ?? null;

        if (! is_array($planConfig) || empty($planConfig['modules'])) {
            return null;
        }

        $quote = app(EntitlementPricer::class)->quote($planConfig);

        if ((float) ($quote['period_total'] ?? 0) <= 0) {
            return null;
        }

        $uid = (string) session('registration.payment_uid', '');
        $payment = HyperPayPayment::query()
            ->where('uid', $uid)
            ->where('status', HyperPayPayment::STATUS_PAID)
            ->whereNull('company_id')
            ->first();

        if (! $payment || ! $payment->matchesPlanConfig($planConfig)) {
            throw ValidationException::withMessages([
                'plan_config' => __('main.wizard.payment_required'),
            ]);
        }

        return $payment;
    }

    protected function attachCardPayment(Company $company, ?HyperPayPayment $payment): void
    {
        if (! $payment) {
            return;
        }

        $entitlement = $company->fresh()?->entitlement;

        $payment->forceFill([
            'company_id' => $company->id,
            'plan_id' => $entitlement?->subscription?->plan_id,
            'subscription_id' => $entitlement?->subscription_id,
        ])->save();

        PaymentSubscription::query()->create([
            'subscription_id' => $entitlement?->subscription_id,
            'company_id' => $company->id,
            'plan_id' => $entitlement?->subscription?->plan_id,
            'amount' => $payment->amount,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'hyperpay',
            'status' => 'paid',
            'transaction_id' => $payment->payment_id ?: $payment->merchant_transaction_id,
            'paid_amount' => $payment->amount,
            'remaining_amount' => 0,
        ]);

        session()->forget(['registration.payment_uid', 'registration.plan_config']);
    }
}
