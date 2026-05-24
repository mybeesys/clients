<?php

namespace App\Support;

use RuntimeException;

class TenantMigrationPaths
{
    public static function resolve(): array
    {
        $tenantAppPath = env('TENANT_APP_PATH', '../mybeeCompany');

        if (! str_starts_with($tenantAppPath, DIRECTORY_SEPARATOR) && ! preg_match('#^[A-Za-z]:\\\\#', $tenantAppPath)) {
            $tenantAppPath = base_path($tenantAppPath);
        }

        $tenantAppPath = realpath($tenantAppPath);

        if ($tenantAppPath === false) {
            return [];
        }

        $paths = [];

        foreach (config('tenant-app.migration_paths', []) as $relativePath) {
            $absolutePath = realpath($tenantAppPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath));

            if ($absolutePath !== false) {
                $paths[] = $absolutePath;
            }
        }

        return $paths;
    }

    public static function resolveOrFail(): array
    {
        $paths = static::resolve();

        if ($paths === []) {
            $configuredPath = env('TENANT_APP_PATH', '../mybeeCompany');

            throw new RuntimeException(
                "No tenant migration paths found under [{$configuredPath}]. ".
                'Set TENANT_APP_PATH in .env and run: php artisan config:clear'
            );
        }

        return $paths;
    }
}
