<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as FilamentAuthenticateMiddleware;

class FilamentAuthenticate extends FilamentAuthenticateMiddleware
{
    protected function redirectTo($request): ?string
    {
        return route('login');
    }
}
