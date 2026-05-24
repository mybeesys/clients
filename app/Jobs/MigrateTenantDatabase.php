<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Process;
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

        // Run in a fresh PHP process so mybeeCompany/vendor cannot override Symfony Console.
        $result = Process::path(base_path())
            ->timeout(600)
            ->run([
                php_binary(),
                base_path('artisan'),
                'tenants:migrate',
                '--tenants='.$tenantKey,
                '--force',
            ]);

        if (! $result->successful()) {
            throw new RuntimeException(
                'Tenant migration failed: '.trim($result->errorOutput() ?: $result->output())
            );
        }
    }
}
