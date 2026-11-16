<?php

namespace App\Http\Controllers;

use App\Services\CompanyPortalAuth;
use App\Support\CompanyPortalHandoff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CentralLoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, CompanyPortalAuth $portal): RedirectResponse
    {
        $this->ensureIsNotRateLimited($request);

        $data = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'company_code' => ['nullable', 'string', 'max:6'],
            'remember' => ['nullable', 'boolean'],
        ], [], [
            'email' => __('main.company_portal.email_or_user_name'),
            'password' => __('main.company_portal.password'),
            'company_code' => __('main.company_portal.company_code'),
        ]);

        $companyCode = trim((string) ($data['company_code'] ?? ''));

        if ($companyCode !== '' && $portal->normalizeCompanyCode($companyCode) === null) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'company_code' => __('main.company_portal.company_code_invalid'),
            ]);
        }

        $result = $portal->attempt(
            (string) $data['email'],
            (string) $data['password'],
            $companyCode !== '' ? $companyCode : null,
        );

        if ($result === null) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => __('main.company_portal.invalid_credentials'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $remember = $request->boolean('remember');
        $user = $result['user'];
        $companies = $result['companies'];

        // No company code: stay on the central clients admin.
        if ($result['central_staff'] || $companyCode === '') {
            if (! $user) {
                throw ValidationException::withMessages([
                    'email' => __('main.company_portal.invalid_credentials'),
                ]);
            }

            Auth::guard('web')->login($user, $remember);
            $request->session()->regenerate();
            $request->session()->forget(CompanyPortalController::SESSION_KEY);

            return redirect()->intended('/admin');
        }

        $company = $companies->first();

        if ($company === null) {
            throw ValidationException::withMessages([
                'email' => __('main.company_portal.invalid_credentials'),
            ]);
        }

        if ($user && ! $company->has_subscription) {
            Auth::guard('web')->login($user, $remember);
            $request->session()->regenerate();

            return redirect()->route('subscribe');
        }

        if ($user) {
            Auth::guard('web')->login($user, $remember);
        }

        $request->session()->regenerate();
        $request->session()->forget(CompanyPortalController::SESSION_KEY);

        return redirect()->away(CompanyPortalHandoff::url(
            $result['email'],
            $company->tenant_id,
            $company->domain,
        ));
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('main.company_portal.throttled', ['seconds' => $seconds]),
        ]);
    }

    protected function throttleKey(Request $request): string
    {
        return 'central-login:'.strtolower($request->input('email', '')).'|'.$request->ip();
    }
}
