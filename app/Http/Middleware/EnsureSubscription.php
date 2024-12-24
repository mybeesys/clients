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
        if ($user->is_company()) {
            if ($user->company->subscribed) {
                $domain = $user->tenant->domains->first()->domain;
                $protocol = request()->secure() ? 'https://' : 'http://';
                return redirect($protocol . $domain);
            } else {
                return redirect(route('subscribe'));
            }
        }
        return $next($request);
    }
}
