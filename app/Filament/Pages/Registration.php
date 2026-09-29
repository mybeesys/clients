<?php

namespace App\Filament\Pages;

use App\Filament\Forms\RegistrationWizard;
use App\Services\Entitlements\CouponApplicator;
use App\Services\RegistrationService;
use App\Support\TenantApplicationUrl;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Events\Auth\Registered;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register;
use Illuminate\Contracts\Support\Htmlable;

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
