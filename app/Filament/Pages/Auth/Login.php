<?php

namespace App\Filament\Pages\Auth;

use App\Http\Controllers\CompanyPortalController;
use App\Services\CompanyPortalAuth;
use App\Support\CompanyPortalHandoff;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Support\Htmlable;

class Login extends \Filament\Pages\Auth\Login
{
    protected static string $view = 'filament.pages.auth.login';

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

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $result = app(CompanyPortalAuth::class)->attempt(
            (string) ($data['email'] ?? ''),
            (string) ($data['password'] ?? ''),
        );

        if ($result === null) {
            $this->throwFailureValidationException();
        }

        $remember = (bool) ($data['remember'] ?? false);
        $user = $result['user'];
        $companies = $result['companies'];

        if ($result['central_staff']) {
            Filament::auth()->login($user, $remember);

            if (($user instanceof FilamentUser) && (! $user->canAccessPanel(Filament::getCurrentPanel()))) {
                Filament::auth()->logout();
                $this->throwFailureValidationException();
            }

            session()->regenerate();

            return app(LoginResponse::class);
        }

        if ($companies->isEmpty()) {
            if ($user) {
                Filament::auth()->login($user, $remember);
                session()->regenerate();
                $this->redirect(route('subscribe'));

                return null;
            }

            $this->throwFailureValidationException();
        }

        $payload = [
            'email' => $result['email'],
            'user_id' => $user?->id,
            'companies' => $companies->map(fn ($company) => [
                'company_id' => $company->company_id,
                'company_name' => $company->company_name,
                'tenant_id' => $company->tenant_id,
                'domain' => $company->domain,
                'has_subscription' => (bool) $company->has_subscription,
            ])->all(),
        ];

        if ($companies->count() > 1) {
            if ($user) {
                Filament::auth()->login($user, $remember);
                session()->regenerate();
            } else {
                session()->regenerate();
            }

            session()->put(CompanyPortalController::SESSION_KEY, $payload);
            $this->redirect(route('company-portal.choose'));

            return null;
        }

        $company = $companies->first();

        if ($user && ! $company->has_subscription) {
            Filament::auth()->login($user, $remember);
            session()->regenerate();
            $this->redirect(route('subscribe'));

            return null;
        }

        if ($user) {
            Filament::auth()->login($user, $remember);
        }

        session()->regenerate();
        session()->forget(CompanyPortalController::SESSION_KEY);

        $this->redirect(CompanyPortalHandoff::url(
            $result['email'],
            $company->tenant_id,
            $company->domain,
        ));

        return null;
    }
}
