<?php

namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Models\Contracts\FilamentUser;

use Illuminate\Validation\ValidationException;

class Login extends \Filament\Pages\Auth\Login
{
    //auth function for the company subdomain.
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();

        if (!Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();
        if (($user instanceof FilamentUser) && (!$user->canAccessPanel(Filament::getCurrentPanel()))) {
            Filament::auth()->logout();
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        if ($user->is_company()) {
            if ($user->company->subscribed) {
                $domain = $user->tenant->domains->first()->domain;
                $protocol = request()->secure() ? 'https://' : 'http://';
                $this->redirect($protocol . $domain);
                return null;
            } else {
                $this->redirect(route('subscribe'));
                return null;
            }
        }


        return app(LoginResponse::class);
    }

    protected function throwFailureSubscriptionException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('No active subscription found.'),
        ]);
    }

    protected function throwFailureAdminException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('This account is not an admin.'),
        ]);
    }
}
