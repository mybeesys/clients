<?php

namespace App\Jobs;

use App\Support\TenantMigrationPaths;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

use function Illuminate\Support\php_binary;

class MigrateTenantDatabase extends MigrateDatabase
{
    public function __construct(TenantWithDatabase $tenant)
    {
        parent::__construct($tenant);
    }

    public function handle(): void
    {
        $tenantKey = $this->tenant->getTenantKey();
        $paths = TenantMigrationPaths::resolveOrFail();

        $command = [
            php_binary(),
            base_path('artisan'),
            'tenants:migrate',
            '--tenants='.$tenantKey,
            '--force',
            '--realpath',
        ];

        foreach ($paths as $path) {
            $command[] = '--path='.$path;
        }

        $result = Process::path(base_path())
            ->timeout(600)
            ->run($command);

        $this->assertMigrateProcessSucceeded($result);

        $this->tenant->run(function (): void {
            if (! Schema::hasTable('est_establishments')) {
                throw new RuntimeException(
                    'Tenant migrations did not create required tables (est_establishments). '.
                    'Check TENANT_APP_PATH and mybeeCompany module migration folders.'
                );
            }
        });
    }

    /**
     * PHP CLI often prints harmless "Module X is already loaded" to stderr when the
     * extension is enabled twice in php.ini. On some hosts that also yields a non-zero
     * exit code even when tenants:migrate finished. Prefer real migrate/SQL errors, and
     * let the schema check below be the source of truth when only startup noise remains.
     */
    protected function assertMigrateProcessSucceeded(ProcessResult $result): void
    {
        if ($result->successful()) {
            return;
        }

        $stderr = $this->withoutPhpStartupNoise($result->errorOutput());
        $stdout = trim($result->output());

        if ($stderr === '' && ! $this->outputIndicatesFailure($stdout)) {
            return;
        }

        $detail = trim(implode("\n", array_filter([$stderr, $stdout], fn (string $part): bool => $part !== '')));

        throw new RuntimeException(
            'Tenant migration failed: '.($detail !== '' ? $detail : 'exit code '.$result->exitCode())
        );
    }

    protected function withoutPhpStartupNoise(string $output): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $output) ?: [];

        $filtered = array_values(array_filter($lines, function (string $line): bool {
            $line = trim($line);

            if ($line === '') {
                return false;
            }

            return ! preg_match('/^PHP Warning:\s+Module ".*" is already loaded/i', $line);
        }));

        return trim(implode(PHP_EOL, $filtered));
    }

    protected function outputIndicatesFailure(string $stdout): bool
    {
        if ($stdout === '') {
            return false;
        }

        return (bool) preg_match('/(SQLSTATE|Fatal error|Parse error|Exception|ERROR\s+\d+|Migration failed)/i', $stdout);
    }
}
