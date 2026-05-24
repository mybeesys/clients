<?php

namespace App\Providers;

use App\Support\TenantAppAutoloader;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        TenantAppAutoloader::register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->visible(outsidePanels: true)
                ->locales(['ar','en']);
        });
    }
}
