<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\TenantProvisionSteps;
use App\Support\TenantProvisioner;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Finishes every provisioning step in this process.
 * The registration page uses the same steps, a few seconds at a time,
 * so a web timeout cannot stop a new company halfway through.
 */
class ProvisionTenantDatabase
{
    use Dispatchable;

    public function __construct(public string $tenantId) {}

    public function handle(): void
    {
        if (! TenantProvisioner::validTenantId($this->tenantId)) {
            return;
        }

        ignore_user_abort(true);
        set_time_limit(0);
        $this->writePid();

        try {
            $tenant = Tenant::query()->find($this->tenantId);

            if (! $tenant || (string) $tenant->provision_status === 'ready') {
                return;
            }

            if ((string) $tenant->provision_status === 'failed') {
                $tenant->provision_status = 'queued';
                $tenant->provision_error = null;
                $tenant->save();
            }

            app(TenantProvisionSteps::class)->advance($tenant, 600, 100);
        } finally {
            $this->clearOwnPid();
        }
    }

    protected function writePid(): void
    {
        file_put_contents(TenantProvisioner::pidPath($this->tenantId), (string) getmypid(), LOCK_EX);
    }

    protected function clearOwnPid(): void
    {
        $path = TenantProvisioner::pidPath($this->tenantId);

        if (! is_file($path)) {
            return;
        }

        $pid = (int) trim((string) file_get_contents($path));

        if ($pid === getmypid()) {
            unlink($path);
        }
    }
}
