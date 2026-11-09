<?php

namespace App\Services;

use App\Models\Tenant;
use App\Support\TenantAppAutoloader;
use App\Support\TenantDomainName;
use App\Support\TenantMigrationPaths;
use BadMethodCallException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Throwable;

/**
 * Builds a tenant database one step at a time.
 * The registration page calls this from short requests, so a web-server
 * timeout cannot abandon the company before its domain exists.
 */
class TenantProvisionSteps
{
    public function advance(Tenant $tenant, int $budgetSeconds = 20, int $maxSteps = 1): void
    {
        $tenantId = (string) $tenant->getTenantKey();
        $claim = (string) Str::uuid();
        $lockKey = 'tenant-step:'.$tenantId;

        if (! $this->acquire($lockKey, $claim)) {
            return;
        }

        $deadline = microtime(true) + max(5, $budgetSeconds);
        $completed = 0;
        $tenant = $this->useOrderedPlan($tenant);

        try {
            do {
                $tenant = $tenant->fresh() ?? $tenant;
                $status = (string) ($tenant->provision_status ?: 'queued');

                if ($status === 'ready' || $status === 'failed') {
                    return;
                }

                $this->renew($lockKey, $claim);
                $finished = $this->runOne($tenant);
                $completed++;

                if ($finished) {
                    $this->markReady($tenant->fresh() ?? $tenant);

                    return;
                }
            } while ($completed < $maxSteps && microtime(true) < $deadline);
        } catch (Throwable $exception) {
            if (function_exists('tenancy') && tenancy()->initialized) {
                tenancy()->end();
            }

            report($exception);
            $this->markFailed($tenant->fresh() ?? $tenant, $exception);
        } finally {
            $this->release($lockKey, $claim);
        }
    }

    public function detail(Tenant $tenant): string
    {
        try {
            $plan = $this->plan();
        } catch (Throwable) {
            return '';
        }

        $index = min((int) ($tenant->provision_step ?? 0), count($plan) - 1);
        $step = $plan[$index] ?? 'domain';

        if ($step === 'database') {
            return (string) __('main.registration_progress.step_database');
        }

        return (string) __("main.registration_progress.step_{$step}");
    }

    /**
     * @return list<string>
     */
    public function plan(): array
    {
        $steps = ['database', 'migrate', 'verify'];
        $steps[] = 'seed';
        $steps[] = 'domain';

        return $steps;
    }

    protected function runOne(Tenant $tenant): bool
    {
        $plan = $this->plan();
        $index = (int) ($tenant->provision_step ?? 0);
        $step = $plan[$index] ?? 'domain';

        $tenant->provision_status = 'running';
        $tenant->provision_error = null;
        $tenant->save();

        try {
            if ($step === 'database') {
                $this->createDatabase($tenant);
            } elseif ($step === 'migrate') {
                if (! $this->migratePending($tenant)) {
                    return false;
                }
            } elseif ($step === 'verify') {
                $this->assertRequiredTables($tenant);
            } elseif ($step === 'seed') {
                app()->call([new \App\Jobs\SeedTenantDatabase($tenant), 'handle']);
            } else {
                $this->ensureDomain($tenant);
            }
        } finally {
            if (function_exists('tenancy') && tenancy()->initialized) {
                tenancy()->end();
            }
        }

        $tenant->provision_step = $index + 1;
        $tenant->save();

        return $step === 'domain';
    }

    /**
     * Folder-by-folder migration ran a foreign key before the referenced table existed.
     * Restart those companies from the beginning; already applied files are skipped.
     */
    protected function useOrderedPlan(Tenant $tenant): Tenant
    {
        if (($tenant->provision_plan ?? null) === 'ordered') {
            return $tenant;
        }

        $tenant->provision_plan = 'ordered';
        $tenant->provision_step = 0;
        $tenant->provision_status = 'queued';
        $tenant->provision_error = null;
        $tenant->save();

        return $tenant->fresh() ?? $tenant;
    }

    protected function migratePending(Tenant $tenant): bool
    {
        $deadline = microtime(true) + 12;

        do {
            $next = $this->nextPendingFile($tenant);

            if ($next === null) {
                return true;
            }

            $this->migratePath($tenant, $next);
        } while (microtime(true) < $deadline);

        return false;
    }

    protected function nextPendingFile(Tenant $tenant): ?string
    {
        $ran = $tenant->run(function (): array {
            if (! Schema::hasTable('migrations')) {
                return [];
            }

            return \Illuminate\Support\Facades\DB::table('migrations')->pluck('migration')->all();
        });

        if (function_exists('tenancy') && tenancy()->initialized) {
            tenancy()->end();
        }

        $done = array_fill_keys($ran, true);

        foreach (TenantMigrationPaths::files() as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);

            if (! isset($done[$name])) {
                return $file;
            }
        }

        return null;
    }

    protected function createDatabase(Tenant $tenant): void
    {
        try {
            $created = app()->call([new CreateDatabase($tenant), 'handle']);
        } catch (Throwable $exception) {
            if (! $this->databaseExists($tenant)) {
                throw $exception;
            }

            return;
        }

        if ($created === false && ! $this->databaseExists($tenant)) {
            throw new RuntimeException('Tenant database creation was skipped.');
        }
    }

    protected function migratePath(Tenant $tenant, string $path): void
    {
        TenantAppAutoloader::register();

        try {
            $exitCode = Artisan::call('tenants:migrate', [
                '--tenants' => [(string) $tenant->getTenantKey()],
                '--force' => true,
                '--path' => [$path],
                '--realpath' => true,
            ]);
            $output = trim(Artisan::output());
        } finally {
            if (function_exists('tenancy') && tenancy()->initialized) {
                tenancy()->end();
            }
        }

        if ($exitCode !== 0 || preg_match('/SQLSTATE|Migration failed|Fatal error/i', $output) === 1) {
            throw new RuntimeException($output !== '' ? $output : 'Tenant migration failed.');
        }
    }

    protected function assertRequiredTables(Tenant $tenant): void
    {
        $tenant->run(function (): void {
            $required = [
                'est_establishments',
                'emp_activity_logs',
                'franchise_companies',
                'screen_devices',
                'reservation_areas',
                'expenses',
                'zatca_settings',
            ];

            $missing = array_values(array_filter(
                $required,
                static fn (string $table): bool => ! Schema::hasTable($table),
            ));

            if ($missing !== []) {
                throw new RuntimeException(
                    'Tenant migrations did not create required tables ('.implode(', ', $missing).').'
                );
            }
        });
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

    protected function markReady(Tenant $tenant): void
    {
        $tenant->provision_status = 'ready';
        $tenant->provision_error = null;
        $tenant->save();

        try {
            app(RegistrationService::class)->deliverQueuedWelcomeEmail((string) $tenant->getTenantKey());
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    protected function markFailed(Tenant $tenant, Throwable $exception): void
    {
        $tenant->provision_status = 'failed';
        $tenant->provision_error = mb_substr($exception->getMessage(), 0, 500);
        $tenant->save();
    }

    protected function databaseExists(Tenant $tenant): bool
    {
        try {
            return $tenant->database()->manager()->databaseExists($tenant->database()->getName());
        } catch (Throwable) {
            return false;
        }
    }

    protected function acquire(string $lockKey, string $claim): bool
    {
        try {
            return Cache::add($lockKey, $claim, now()->addSeconds(90)) === true;
        } catch (BadMethodCallException) {
            return true;
        }
    }

    protected function renew(string $lockKey, string $claim): void
    {
        try {
            if (Cache::get($lockKey) === $claim) {
                Cache::put($lockKey, $claim, now()->addSeconds(90));
            }
        } catch (BadMethodCallException) {
            //
        }
    }

    protected function release(string $lockKey, string $claim): void
    {
        try {
            if (Cache::get($lockKey) === $claim) {
                Cache::forget($lockKey);
            }
        } catch (BadMethodCallException) {
            //
        }
    }
}
