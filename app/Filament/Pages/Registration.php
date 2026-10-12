<?php

namespace App\Filament\Pages;

use App\Filament\Forms\RegistrationWizard;
use App\Models\HyperPayPayment;
use App\Services\Entitlements\CouponApplicator;
use App\Services\Entitlements\EntitlementPricer;
use App\Services\HyperPay\HyperPayCheckoutService;
use App\Services\HyperPay\HyperPayConfig;
use App\Services\RegistrationService;
use App\Support\TenantApplicationUrl;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Events\Auth\Registered;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class Registration extends Register
{
    protected static string $view = 'filament.pages.auth.register';

    protected ?string $maxWidth = '7xl';

    public function mount(): void
    {
        parent::mount();

        $ref = request()->query('ref');

        if (is_string($ref) && $ref !== '') {
            session(['referral_code' => strtoupper(trim($ref))]);
        }

        $paid = $this->paidRegistrationPayment();

        if ($paid && is_array($paid->plan_config)) {
            $this->form->fill([
                'plan_config' => $paid->plan_config,
            ]);
        }

        $step = (string) request()->query('step', 'plan');

        if (in_array($step, ['company', 'account'], true) && ! $this->hasSatisfiedPlanPayment()) {
            $this->redirect(url('/admin/register?step=plan'));
        }
    }

    public function startRegistrationPayment(): void
    {
        $planConfig = data_get($this->data, 'plan_config', []);

        try {
            $quote = app(EntitlementPricer::class)->quote(is_array($planConfig) ? $planConfig : []);
        } catch (\Throwable $exception) {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();

            throw new Halt();
        }

        if ((float) ($quote['period_total'] ?? 0) <= 0) {
            session([
                'registration.payment_uid' => 'free',
                'registration.plan_config' => $planConfig,
            ]);

            return;
        }

        $existing = $this->paidRegistrationPayment();

        if ($existing && $existing->matchesPlanConfig($planConfig)) {
            return;
        }

        if (! HyperPayConfig::instance()->isConfigured()) {
            Notification::make()
                ->title(__('fields.hyperpay_not_configured'))
                ->danger()
                ->send();

            throw new Halt();
        }

        $token = (string) session('registration.session_token', '');

        if ($token === '') {
            $token = Str::random(40);
            session(['registration.session_token' => $token]);
        }

        try {
            $payment = HyperPayCheckoutService::instance()->startRegistration(
                $planConfig,
                $quote,
                HyperPayCheckoutService::placeholderBilling(),
                $token,
            );
        } catch (InvalidArgumentException|RuntimeException $exception) {
            Notification::make()
                ->title(__('fields.hyperpay_checkout_failed'))
                ->body($exception->getMessage())
                ->danger()
                ->send();

            throw new Halt();
        }

        session([
            'registration.payment_uid' => $payment->uid,
            'registration.plan_config' => $planConfig,
        ]);

        $this->redirect(route('hyperpay.checkout', ['uid' => $payment->uid]), navigate: false);

        throw new Halt();
    }

    public function hasSatisfiedPlanPayment(): bool
    {
        $planConfig = data_get($this->data, 'plan_config')
            ?? session('registration.plan_config');

        if (! is_array($planConfig) || empty($planConfig['modules'])) {
            $planConfig = session('registration.plan_config');
        }

        if (! is_array($planConfig)) {
            return false;
        }

        try {
            $quote = app(EntitlementPricer::class)->quote($planConfig);
        } catch (\Throwable) {
            return false;
        }

        if ((float) ($quote['period_total'] ?? 0) <= 0) {
            return true;
        }

        $paid = $this->paidRegistrationPayment();

        return $paid !== null && $paid->matchesPlanConfig($planConfig);
    }

    public function paidRegistrationPayment(): ?HyperPayPayment
    {
        $uid = (string) session('registration.payment_uid', '');

        if ($uid === '' || $uid === 'free') {
            return null;
        }

        $payment = HyperPayPayment::query()
            ->where('uid', $uid)
            ->where('status', HyperPayPayment::STATUS_PAID)
            ->whereNull('company_id')
            ->first();

        if (! $payment) {
            return null;
        }

        $token = (string) session('registration.session_token', '');

        if ($payment->session_token && $token !== '' && ! hash_equals((string) $payment->session_token, $token)) {
            return null;
        }

        return $payment;
    }

    public function form(Form $form): Form
    {
        return RegistrationWizard::configure($form);
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    /**
     * Live coupon preview for the plan configurator invoice.
     *
     * @return array{ok: bool, message?: string, code?: string, type?: string, value?: float, discount?: float, label?: string}
     */
    public function previewPlanCoupon(?string $code = null, float $amount = 0): array
    {
        return app(CouponApplicator::class)->preview($code, max(0, $amount));
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        $user = app(RegistrationService::class)->register($data);

        $user->load(['company', 'tenant.domains']);

        event(new Registered($user));
        $this->sendEmailVerificationNotification($user);

        session()->flash('registration_thank_you', [
            'company_name' => $user->company?->name,
            'tenant_url' => TenantApplicationUrl::forUser($user),
            'email' => $user->email,
        ]);

        return app(RegistrationResponse::class);
    }

    public function getFormActions(): array
    {
        return [];
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label(__('filament-panels::pages/auth/register.actions.login.label'))
            ->url('/');
    }
}
