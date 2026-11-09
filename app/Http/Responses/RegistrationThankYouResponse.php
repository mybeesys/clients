<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class RegistrationThankYouResponse implements RegistrationResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        if (session()->has('registration.pending')) {
            return redirect()->route('register.pending');
        }

        return redirect()->route('register.thank-you');
    }
}
