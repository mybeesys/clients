<?php

return [

    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    Modules\Company\Providers\Filament\CompanyPanelProvider::class,
    Modules\Company\Providers\TenancyServiceProvider::class,
    App\Providers\TenantServiceProvider::class,
    Nwidart\Modules\LaravelModulesServiceProvider::class,

];
