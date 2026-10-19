<?php

namespace App\Http\Controllers;

use App\Models\HyperPayPayment;
use App\Services\HyperPay\HyperPayCheckoutService;
use App\Services\HyperPay\HyperPayConfig;
use App\Support\TenantApplicationUrl;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class HyperPayCheckoutController extends Controller
{
    public function show(Request $request, string $uid): View|RedirectResponse
    {
        $payment = $this->ownedPayment($uid);

        if ($payment->isPaid()) {
            return $this->finishPaid($payment);
        }

        if ($payment->status === HyperPayPayment::STATUS_FAILED) {
            return $this->finishFailed($payment, $payment->result_description);
        }

        $config = HyperPayConfig::instance();
        $service = HyperPayCheckoutService::instance();
        $quote = is_array($payment->quote) ? $payment->quote : [];
        $billing = is_array($payment->billing) ? $payment->billing : [];
        $isSubscription = $payment->source === HyperPayPayment::SOURCE_SUBSCRIPTION;
        $isSaveCard = $payment->source === HyperPayPayment::SOURCE_SAVE_CARD;

        return view('hyperpay.checkout', [
            'payment' => $payment,
            'config' => $config,
            'widgetUrl' => $service->widgetScriptUrl($payment),
            'resultUrl' => route('hyperpay.result', ['uid' => $payment->uid]),
            'cancelUrl' => $this->returnUrl($payment, failed: false),
            'brands' => $config->brands(),
            'isRegistration' => $payment->source === HyperPayPayment::SOURCE_REGISTRATION,
            'isSubscription' => $isSubscription,
            'isSaveCard' => $isSaveCard,
            'quote' => $this->viewQuote($payment, $quote),
            'cardholder' => trim(($billing['given_name'] ?? '').' '.($billing['surname'] ?? '')),
        ]);
    }

    public function result(Request $request, string $uid): RedirectResponse
    {
        $payment = $this->ownedPayment($uid);
        $resourcePath = (string) $request->query('resourcePath', $request->input('resourcePath', ''));

        if ($resourcePath === '') {
            return $this->finishFailed($payment, __('fields.hyperpay_missing_resource'));
        }

        try {
            $payment = HyperPayCheckoutService::instance()->complete($payment, $resourcePath);
        } catch (RuntimeException $exception) {
            return $this->finishFailed($payment, $exception->getMessage());
        }

        if ($payment->isPaid()) {
            return $this->finishPaid($payment);
        }

        if ($payment->status === HyperPayPayment::STATUS_PENDING_GATEWAY) {
            Notification::make()
                ->title(__('fields.hyperpay_payment_pending_title'))
                ->body(__('fields.hyperpay_payment_pending_body'))
                ->warning()
                ->send();

            return redirect()->to($this->returnUrl($payment, failed: false))
                ->with('error', __('fields.hyperpay_payment_pending_body'));
        }

        return $this->finishFailed($payment, $payment->result_description ?: __('fields.hyperpay_payment_failed_body'));
    }

    protected function ownedPayment(string $uid): HyperPayPayment
    {
        $payment = HyperPayPayment::query()
            ->where('uid', $uid)
            ->firstOrFail();

        if (in_array($payment->source, [
            HyperPayPayment::SOURCE_SUBSCRIPTION,
            HyperPayPayment::SOURCE_SAVE_CARD,
        ], true)) {
            abort_unless($this->ownsCompanyPayment($payment), 403);

            return $payment;
        }

        $sessionUid = (string) session('registration.payment_uid', '');
        $sessionToken = (string) session('registration.session_token', '');

        abort_unless(
            hash_equals((string) $payment->uid, $sessionUid)
            || ($payment->session_token && $sessionToken !== '' && hash_equals((string) $payment->session_token, $sessionToken)),
            403
        );

        return $payment;
    }

    protected function ownsCompanyPayment(HyperPayPayment $payment): bool
    {
        $sessionUid = (string) session('hyperpay.checkout_uid', '');
        $sessionToken = (string) session('hyperpay.checkout_token', '');

        if ($sessionUid !== '' && hash_equals((string) $payment->uid, $sessionUid)) {
            return true;
        }

        if ($payment->session_token && $sessionToken !== '' && hash_equals((string) $payment->session_token, $sessionToken)) {
            return true;
        }

        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $company = $user->defaultCompany() ?? $user->company;

        return $company && (int) $payment->company_id === (int) $company->id;
    }

    protected function finishPaid(HyperPayPayment $payment): RedirectResponse
    {
        if ($payment->source === HyperPayPayment::SOURCE_SAVE_CARD) {
            session()->forget(['hyperpay.checkout_uid', 'hyperpay.checkout_token']);

            Notification::make()
                ->title(__('fields.hyperpay_payment_success_title'))
                ->body(__('fields.hyperpay_payment_success_save_card'))
                ->success()
                ->send();

            return redirect()->away($this->tenantCardsUrl($payment));
        }

        if ($payment->source === HyperPayPayment::SOURCE_SUBSCRIPTION) {
            try {
                HyperPayCheckoutService::instance()->fulfillSubscription($payment);
            } catch (\Throwable $exception) {
                report($exception);

                return redirect()->route('subscribe')
                    ->with('error', $exception->getMessage());
            }

            session()->forget(['hyperpay.checkout_uid', 'hyperpay.checkout_token']);

            Notification::make()
                ->title(__('fields.hyperpay_payment_success_title'))
                ->body(__('fields.hyperpay_payment_success_subscription'))
                ->success()
                ->send();

            $company = $payment->company;
            $url = $company ? TenantApplicationUrl::forCompany($company) : null;

            if ($url) {
                return redirect()->away($url.'/subscription');
            }

            return redirect()->route('subscribe')->with('status', __('main.subscribe.updated'));
        }

        session([
            'registration.payment_uid' => $payment->uid,
            'registration.plan_config' => $payment->plan_config,
        ]);

        Notification::make()
            ->title(__('fields.hyperpay_payment_success_title'))
            ->body(__('main.wizard.payment_success_continue'))
            ->success()
            ->send();

        return redirect()->to($this->registerUrl('company'));
    }

    protected function finishFailed(HyperPayPayment $payment, ?string $reason = null): RedirectResponse
    {
        $message = $reason ?: __('fields.hyperpay_payment_failed_body');

        Notification::make()
            ->title(__('fields.hyperpay_payment_failed_title'))
            ->body($message)
            ->danger()
            ->send();

        if ($payment->source === HyperPayPayment::SOURCE_SAVE_CARD) {
            return redirect()->away($this->tenantCardsUrl($payment))
                ->with('error', $message);
        }

        if ($payment->source === HyperPayPayment::SOURCE_SUBSCRIPTION) {
            return redirect()->route('subscribe')->with('error', $message);
        }

        return redirect()->to($this->registerUrl('plan'));
    }

    protected function returnUrl(HyperPayPayment $payment, bool $failed): string
    {
        if ($payment->source === HyperPayPayment::SOURCE_SAVE_CARD) {
            return $this->tenantCardsUrl($payment);
        }

        if ($payment->source === HyperPayPayment::SOURCE_SUBSCRIPTION) {
            return route('subscribe');
        }

        return $this->registerUrl($failed ? 'plan' : 'plan');
    }

    protected function tenantCardsUrl(HyperPayPayment $payment): string
    {
        $company = $payment->company;
        $url = $company ? TenantApplicationUrl::forCompany($company) : null;

        if ($url) {
            return $url.'/subscription?tab=cards';
        }

        return route('subscribe');
    }

    protected function registerUrl(string $step): string
    {
        return url('/admin/register?step='.$step);
    }

    /**
     * @param  array<string, mixed>  $quote
     * @return array<string, mixed>
     */
    protected function viewQuote(HyperPayPayment $payment, array $quote): array
    {
        $total = (float) ($quote['period_total'] ?? $payment->amount);
        $subtotal = (float) ($quote['period_subtotal'] ?? $total);
        $discount = (float) ($quote['discount'] ?? 0);

        return [
            'subtotal_ex_tax' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => 0.0,
            'tax_percent' => 0.0,
            'total_inc_tax' => $total,
            'currency' => (string) ($quote['currency'] ?? $payment->currency ?? 'SAR'),
        ];
    }
}
