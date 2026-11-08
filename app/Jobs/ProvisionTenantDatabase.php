<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\RegistrationService;
use BadMethodCallException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Throwable;

/**
 * Builds the tenant database after the HTTP response has already been sent.
 * Running this inside the registration request holds the browser on the
 * submit spinner until every module migration and the country seed finish.
 */
class ProvisionTenantDatabase
{
    use Dispatchable;

    public function __construct(public string $tenantId) {}

    public function handle(): void
    {
        $lockKey = 'tenant-provisioning:'.$this->tenantId;

        try {
            if (! Cache::add($lockKey, now()->toIso8601String(), now()->addMinutes(20))) {
                Log::info('Tenant provisioning already running.', ['tenant' => $this->tenantId]);

                return;
            }
        } catch (BadMethodCallException) {
            $lockKey = null;
        }

        set_time_limit(0);

        try {
            $tenant = Tenant::query()->find($this->tenantId);

            if (! $tenant) {
                return;
            }

            Log::info('Tenant provisioning started.', ['tenant' => $this->tenantId]);

            $created = app()->call([new CreateDatabase($tenant), 'handle']);

            if ($created !== false) {
                app()->call([new MigrateTenantDatabase($tenant), 'handle']);
                app()->call([new SeedTenantDatabase($tenant), 'handle']);
                Log::info('Tenant provisioning finished.', ['tenant' => $this->tenantId]);
            }
        } catch (Throwable $exception) {
            report($exception);
        } finally {
            if ($lockKey !== null) {
                try {
                    Cache::forget($lockKey);
                } catch (BadMethodCallException) {
                    //
                }
            }
        }

        try {
            app(RegistrationService::class)->deliverQueuedWelcomeEmail($this->tenantId);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
