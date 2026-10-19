<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Subscribe UI is often loaded in a cross-origin (same-site) iframe on tenant hosts.
 * Default SameSite=Lax cookies can fail on subsequent Livewire POSTs inside that iframe,
 * which surfaces as HTTP 419. Force None+Secure for subscribe-related traffic.
 *
 * Must run before StartSession (prepended on the web stack) — do not read session here.
 */
class ConfigureSubscribeEmbedSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldUseNoneSameSite($request)) {
            config([
                'session.same_site' => 'none',
                'session.secure' => true,
            ]);
        }

        return $next($request);
    }

    protected function shouldUseNoneSameSite(Request $request): bool
    {
        if ($request->boolean('embed')) {
            return true;
        }

        if ($request->is('subscribe/handoff/*')) {
            return true;
        }

        $dest = strtolower((string) $request->headers->get('Sec-Fetch-Dest', ''));
        if (in_array($dest, ['iframe', 'nested'], true) && $request->is(
            'subscribe',
            'subscribe/*',
            'pay/hyperpay',
            'pay/hyperpay/*',
            'livewire/update',
            'livewire/*',
        )) {
            return true;
        }

        $referer = (string) $request->headers->get('Referer', '');
        if ($referer !== '' && (
            str_contains($referer, '/subscribe')
            || str_contains($referer, '/pay/hyperpay')
        ) && $request->is(
            'livewire/update',
            'livewire/*',
            'subscribe',
            'subscribe/*',
            'pay/hyperpay',
            'pay/hyperpay/*',
        )) {
            return true;
        }

        return false;
    }
}
