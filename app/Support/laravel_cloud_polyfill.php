<?php

if (! function_exists('laravel_cloud')) {
    function laravel_cloud(): bool
    {
        return ($_ENV['LARAVEL_CLOUD'] ?? false) === '1'
            || ($_SERVER['LARAVEL_CLOUD'] ?? false) === '1';
    }
}
