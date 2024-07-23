<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\CompanyPanelProvider::class,
    // App\Providers\TenantServiceProvider::class,
    Modules\Company\Providers\TenancyServiceProvider::class,
    Nwidart\Modules\LaravelModulesServiceProvider::class,
];
