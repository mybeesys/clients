<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\RegistrationService;
use Illuminate\Foundation\Bus\Dispatchable;
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
        set_time_limit(0);

        $tenant = Tenant::query()->find($this->tenantId);

        if (! $tenant) {
            return;
        }

        Log::info('Tenant provisioning started.', ['tenant' => $this->tenantId]);

        try {
            $created = app()->call([new CreateDatabase($tenant), 'handle']);

            if ($created !== false) {
                app()->call([new MigrateTenantDatabase($tenant), 'handle']);
                app()->call([new SeedTenantDatabase($tenant), 'handle']);
                Log::info('Tenant provisioning finished.', ['tenant' => $this->tenantId]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        try {
            app(RegistrationService::class)->deliverQueuedWelcomeEmail();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
