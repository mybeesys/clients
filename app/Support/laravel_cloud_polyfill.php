<?php

/*
|--------------------------------------------------------------------------
| Laravel Cloud helper polyfill
|--------------------------------------------------------------------------
|
| Newer Laravel framework versions call laravel_cloud() from namespaced
| middleware before Support helpers are loaded. PHP then looks for
| Illuminate\Http\Middleware\laravel_cloud() instead of the global helper.
|
*/

if (! function_exists('laravel_cloud')) {
    function laravel_cloud(): bool
    {
        return ($_ENV['LARAVEL_CLOUD'] ?? false) === '1'
            || ($_SERVER['LARAVEL_CLOUD'] ?? false) === '1';
    }
}

namespace Illuminate\Http\Middleware;

if (! function_exists(__NAMESPACE__.'\\laravel_cloud')) {
    function laravel_cloud(): bool
    {
        return \laravel_cloud();
    }
}

namespace Illuminate\Foundation;

if (! function_exists(__NAMESPACE__.'\\laravel_cloud')) {
    function laravel_cloud(): bool
    {
        return \laravel_cloud();
    }
}
