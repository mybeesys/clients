<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale', 'ar'));

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        session()->put('locale', $locale);
        app()->setLocale($locale);

        return $next($request);
    }
}
