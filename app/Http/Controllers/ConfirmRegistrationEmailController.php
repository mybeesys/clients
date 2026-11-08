<?php

namespace App\Http\Controllers;

use App\Services\RegistrationEmailVerifier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfirmRegistrationEmailController extends Controller
{
    public function __invoke(Request $request, RegistrationEmailVerifier $verifier): View
    {
        $email = (string) $request->query('email', '');
        $sessionId = (string) $request->query('sid', '');

        $verifier->markVerified($sessionId, $email);

        return view('auth.registration-email-confirmed', [
            'email' => $verifier->normalize($email),
        ]);
    }
}
