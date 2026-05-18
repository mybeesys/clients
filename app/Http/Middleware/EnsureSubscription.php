<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $company = $user->company;
        $domain = $user->tenant?->domains?->first()?->domain;

        if ($user->is_company() && $company && $domain) {
            if ($company->subscription) {
                $protocol = request()->secure() ? 'https://' : 'http://';

                return redirect($protocol . $domain);
            }

            return redirect(route('subscribe'));
        }
        return $next($request);
    }
}
