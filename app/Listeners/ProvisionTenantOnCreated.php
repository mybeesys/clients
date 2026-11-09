<?php

namespace App\Listeners;

use App\Jobs\ProvisionTenantDatabase;
use App\Models\Tenant;
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

        // Start after the request has stored the welcome message, while the
        // response is still the short registration redirect.
        app()->terminating(function () use ($tenantId): void {
            if (TenantProvisioner::start($tenantId)) {
                return;
            }

            $pending = Tenant::query()->find($tenantId);

            if ($pending && (string) $pending->provision_status === 'queued') {
                $pending->provision_status = 'failed';
                $pending->provision_error = 'provision_start_failed';
                $pending->save();
            }
        });
    }
}
