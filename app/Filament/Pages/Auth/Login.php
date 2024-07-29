<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Models\Contracts\FilamentUser;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

use LucasDotVin\Soulbscription\Models\Subscription;

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

        if (!$user->is_admin()) {
            Filament::auth()->logout();
            $this->throwFailureAdminException();
        }


        if ($user->is_company()) {
            $subscription = Subscription::latest()->first();
            if (is_null($subscription) || Carbon::parse($subscription->expired_at)->isPast()) {
                Filament::auth()->logout();
                $this->throwFailureSubscriptionException();
            }
        }

        session()->regenerate();

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
