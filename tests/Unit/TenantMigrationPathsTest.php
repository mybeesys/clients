<?php

use App\Support\TenantMigrationPaths;
use Illuminate\Support\Facades\Config;

uses(Tests\TestCase::class);

test('tenant migration paths keep the configured folder case', function () {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'mb-tenant-paths-'.uniqid();
    $purchases = $root.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.'Purchases'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.'tenant';
    $employee = $root.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.'Employee'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.'tenant';

    mkdir($purchases, 0777, true);
    mkdir($employee, 0777, true);

    Config::set('tenant-app.migration_paths', [
        'Modules/Employee/database/migrations/tenant',
        'Modules/purchases/database/migrations/tenant',
    ]);

    $repository = \Illuminate\Support\Env::getRepository();
    $repository->set('TENANT_APP_PATH', $root);

    try {
        expect(fn () => TenantMigrationPaths::resolveOrFail())
            ->toThrow(RuntimeException::class, 'Modules/purchases/database/migrations/tenant');

        Config::set('tenant-app.migration_paths', [
            'Modules/Employee/database/migrations/tenant',
            'Modules/Purchases/database/migrations/tenant',
        ]);

        $paths = TenantMigrationPaths::resolveOrFail();

        expect($paths)->toHaveCount(2)
            ->and($paths[0])->toEndWith('Employee'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.'tenant')
            ->and($paths[1])->toEndWith('Purchases'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.'tenant');
    } finally {
        \Illuminate\Support\Env::getRepository()->clear('TENANT_APP_PATH');
    }
});
