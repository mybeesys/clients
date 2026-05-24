<?php

namespace App\Jobs;

use App\Support\TenantAppAutoloader;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class MigrateTenantDatabase extends MigrateDatabase
{
    public function handle(): void
    {
        TenantAppAutoloader::register();

        Artisan::call('tenants:migrate', [
            '--tenants' => [$this->tenant->getTenantKey()],
        ]);
    }

    public function __construct(TenantWithDatabase $tenant)
    {
        parent::__construct($tenant);
    }
}
