<?php

namespace App\Console\Commands;

use App\Jobs\ProvisionTenantDatabase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ProvisionTenantCommand extends Command
{
    protected $signature = 'tenants:provision {tenant : Tenant id}';

    protected $description = 'Create, migrate, and seed one tenant database';

    public function handle(): int
    {
        $tenantId = (string) $this->argument('tenant');
        Cache::forget('tenant-provisioning:'.$tenantId);
        (new ProvisionTenantDatabase($tenantId))->handle();

        $this->info('Provisioned tenant '.$tenantId.'.');

        return self::SUCCESS;
    }
}
