<?php

return [

    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\CompanyPanelProvider::class,
    Modules\Company\Providers\TenancyServiceProvider::class,
    App\Providers\TenantServiceProvider::class,
    Filament\FilamentServiceProvider::class,
    Nwidart\Modules\LaravelModulesServiceProvider::class,

];
