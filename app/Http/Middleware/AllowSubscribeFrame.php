<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowSubscribeFrame
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $ancestors = trim((string) config('subscribe.frame_ancestors', ''));
        if ($ancestors === '') {
            return $response;
        }

        $response->headers->remove('X-Frame-Options');
        $response->headers->set(
            'Content-Security-Policy',
            'frame-ancestors \'self\' '.$ancestors
        );

        return $response;
    }
}
