<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\RegistrationService;
use App\Support\TenantDomainName;
use App\Support\TenantProvisioner;
use BadMethodCallException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Throwable;

/**
 * Creates, migrates, and seeds a tenant database, then publishes its domain.
 * The public registration page starts this in a separate process so a web
 * server timeout cannot stop it halfway through.
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

        $lockKey = 'tenant-provisioning:'.$this->tenantId;
        $locked = false;

        try {
            $locked = Cache::add($lockKey, now()->toIso8601String(), now()->addMinutes(20));
        } catch (BadMethodCallException) {
            $locked = true;
        }

        if ($locked !== true) {
            Log::info('Tenant provisioning already running.', ['tenant' => $this->tenantId]);

            return;
        }

        $this->writePid();
        set_time_limit(0);
        $ready = false;

        try {
            $tenant = Tenant::query()->find($this->tenantId);

            if (! $tenant) {
                return;
            }

            $this->mark($tenant, 'running');
            Log::info('Tenant provisioning started.', ['tenant' => $this->tenantId]);

            $created = app()->call([new CreateDatabase($tenant), 'handle']);

            if ($created === false) {
                throw new RuntimeException('Tenant database creation was skipped.');
            }

            app()->call([new MigrateTenantDatabase($tenant), 'handle']);
            app()->call([new SeedTenantDatabase($tenant), 'handle']);
            $this->ensureDomain($tenant->fresh() ?? $tenant);
            $this->mark($tenant->fresh() ?? $tenant, 'ready');
            $ready = true;
            Log::info('Tenant provisioning finished.', ['tenant' => $this->tenantId]);
        } catch (Throwable $exception) {
            report($exception);
            $tenant = Tenant::query()->find($this->tenantId);

            if ($tenant) {
                $this->mark($tenant, 'failed', $exception->getMessage());
            }
        } finally {
            try {
                Cache::forget($lockKey);
            } catch (BadMethodCallException) {
                //
            }

            $this->clearOwnPid();
        }

        if (! $ready) {
            return;
        }

        try {
            app(RegistrationService::class)->deliverQueuedWelcomeEmail($this->tenantId);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    protected function ensureDomain(Tenant $tenant): void
    {
        $tenant->load('domains');

        if ($tenant->domains->isNotEmpty()) {
            return;
        }

        $tenantKey = (string) $tenant->getTenantKey();

        Domain::create([
            'domain' => TenantDomainName::forTenantKey($tenantKey),
            'tenant_id' => $tenantKey,
        ]);
    }

    protected function mark(Tenant $tenant, string $status, ?string $error = null): void
    {
        $tenant->provision_status = $status;
        $tenant->provision_error = $error === null ? null : mb_substr($error, 0, 500);
        $tenant->save();
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
