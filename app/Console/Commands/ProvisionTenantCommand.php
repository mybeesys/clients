<?php

namespace App\Console\Commands;

use App\Jobs\ProvisionTenantDatabase;
use Illuminate\Console\Command;

class ProvisionTenantCommand extends Command
{
    protected $signature = 'tenants:provision {tenant : Tenant id}';

    protected $description = 'Create, migrate, and seed one tenant database';

    public function handle(): int
    {
        (new ProvisionTenantDatabase((string) $this->argument('tenant')))->handle();

        return self::SUCCESS;
    }
}
