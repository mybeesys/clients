<?php

namespace Illuminate\Http\Middleware;

if (! function_exists(__NAMESPACE__.'\\laravel_cloud')) {
    function laravel_cloud(): bool
    {
        return \laravel_cloud();
    }
}
