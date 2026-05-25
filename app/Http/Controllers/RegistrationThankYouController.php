<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationThankYouController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $data = session()->pull('registration_thank_you');

        if (! is_array($data)) {
            return redirect()->route('filament.admin.auth.login');
        }

        return view('auth.registration-thank-you', $data);
    }
}
