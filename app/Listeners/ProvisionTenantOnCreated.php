<?php

namespace App\Listeners;

use App\Jobs\ProvisionTenantDatabase;
use App\Support\TenantProvisioner;
use Stancl\Tenancy\Events\TenantCreated;

class ProvisionTenantOnCreated
{
    public function handle(TenantCreated $event): void
    {
        $tenant = $event->tenant;
        $tenantId = (string) $tenant->getTenantKey();
        $tenant->provision_status = 'queued';
        $tenant->provision_error = null;
        $tenant->save();

        if (app()->runningUnitTests()) {
            return;
        }

        if (app()->runningInConsole()) {
            (new ProvisionTenantDatabase($tenantId))->handle();

            return;
        }

        // A detached process can finish the work if the browser is closed.
        // If it cannot start, the progress page continues the same steps itself.
        app()->terminating(function () use ($tenantId): void {
            TenantProvisioner::start($tenantId);
        });
    }
}
