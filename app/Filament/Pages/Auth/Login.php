<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

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

        $user = User::findByLoginIdentifier($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            $this->throwFailureValidationException();
        }

        Filament::auth()->login($user, $data['remember'] ?? false);
        if (($user instanceof FilamentUser) && (! $user->canAccessPanel(Filament::getCurrentPanel()))) {
            Filament::auth()->logout();
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        $company = $user->defaultCompany();
        $domain = $user->defaultTenant()?->domains?->first()?->domain;

        if ($user->is_company() && $company && $domain) {
            if ($company->subscribed) {
                $protocol = request()->secure() ? 'https://' : 'http://';
                $this->redirect($protocol.$domain);

                return null;
            }

            $this->redirect(route('subscribe'));

            return null;
        }

        return app(LoginResponse::class);
    }
}
