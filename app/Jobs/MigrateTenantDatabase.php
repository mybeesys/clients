<?php

namespace App\Jobs;

use App\Support\TenantMigrationPaths;
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

        if (! $result->successful()) {
            throw new RuntimeException(
                'Tenant migration failed: '.trim($result->errorOutput() ?: $result->output())
            );
        }

        $this->tenant->run(function (): void {
            if (! Schema::hasTable('est_establishments')) {
                throw new RuntimeException(
                    'Tenant migrations did not create required tables (est_establishments). '.
                    'Check TENANT_APP_PATH and mybeeCompany module migration folders.'
                );
            }
        });
    }
}
