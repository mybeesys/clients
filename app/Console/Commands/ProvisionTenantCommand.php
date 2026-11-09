<?php

namespace App\Console\Commands;

use App\Jobs\ProvisionTenantDatabase;
use App\Models\Tenant;
use App\Support\TenantProvisioner;
use BadMethodCallException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ProvisionTenantCommand extends Command
{
    protected $signature = 'tenants:provision {tenant : Tenant id}';

    protected $description = 'Create, migrate, and seed one tenant database, then publish its domain';

    public function handle(): int
    {
        $tenantId = (string) $this->argument('tenant');

        if (! TenantProvisioner::validTenantId($tenantId)) {
            $this->error('Invalid tenant id.');

            return self::FAILURE;
        }

        if (TenantProvisioner::processIsAlive($tenantId)) {
            $this->info('Provisioning is already running for '.$tenantId.'.');

            return self::SUCCESS;
        }

        try {
            Cache::forget('tenant-provisioning:'.$tenantId);
        } catch (BadMethodCallException) {
            //
        }

        ignore_user_abort(true);
        (new ProvisionTenantDatabase($tenantId))->handle();

        $status = Tenant::query()->find($tenantId)?->provision_status;

        if ($status === 'ready') {
            $this->info('Provisioned tenant '.$tenantId.'.');

            return self::SUCCESS;
        }

        $this->error('Provisioning did not finish for '.$tenantId.'.');

        return self::FAILURE;
    }
}
