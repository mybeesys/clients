<?php

namespace App\Support;

use RuntimeException;

class TenantMigrationPaths
{
    public static function resolve(): array
    {
        $root = static::tenantAppRoot();

        if ($root === null) {
            return [];
        }

        $paths = [];

        foreach (config('tenant-app.migration_paths', []) as $relativePath) {
            $absolutePath = static::resolveDirectory($root, (string) $relativePath);

            if ($absolutePath !== null) {
                $paths[] = $absolutePath;
            }
        }

        return $paths;
    }

    public static function resolveOrFail(): array
    {
        $root = static::tenantAppRoot();

        if ($root === null) {
            $configuredPath = env('TENANT_APP_PATH', '../mybeeCompany');

            throw new RuntimeException(
                "No tenant migration paths found under [{$configuredPath}]. ".
                'Set TENANT_APP_PATH in .env and run: php artisan config:clear'
            );
        }

        $paths = [];
        $missing = [];

        foreach (config('tenant-app.migration_paths', []) as $relativePath) {
            $relativePath = (string) $relativePath;
            $absolutePath = static::resolveDirectory($root, $relativePath);

            if ($absolutePath === null) {
                $missing[] = $relativePath;

                continue;
            }

            $paths[] = $absolutePath;
        }

        if ($missing !== []) {
            throw new RuntimeException(
                'Tenant migration folders are missing under ['.$root.']: '.implode(', ', $missing).'. '.
                'Names are case-sensitive and must match my-bee-company config/tenancy.php. '.
                'Run: php artisan config:clear'
            );
        }

        if ($paths === []) {
            throw new RuntimeException(
                "No tenant migration paths configured under [{$root}]. ".
                'Set tenant-app.migration_paths and run: php artisan config:clear'
            );
        }

        return $paths;
    }

    /**
     * Walk the configured relative path with exact segment names.
     * realpath() on Windows accepts the wrong case, which then silently drops the folder on Linux.
     */
    protected static function resolveDirectory(string $root, string $relativePath): ?string
    {
        $current = $root;

        foreach (explode('/', str_replace('\\', '/', $relativePath)) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            $matched = null;

            foreach (scandir($current) ?: [] as $entry) {
                if ($entry === $segment) {
                    $matched = $entry;
                    break;
                }
            }

            if ($matched === null) {
                return null;
            }

            $current .= DIRECTORY_SEPARATOR.$matched;

            if (! is_dir($current)) {
                return null;
            }
        }

        return $current;
    }

    protected static function tenantAppRoot(): ?string
    {
        $tenantAppPath = env('TENANT_APP_PATH', '../mybeeCompany');

        if (! is_string($tenantAppPath) || $tenantAppPath === '') {
            return null;
        }

        if (! str_starts_with($tenantAppPath, DIRECTORY_SEPARATOR) && ! preg_match('#^[A-Za-z]:[\\\\/]#', $tenantAppPath)) {
            $tenantAppPath = base_path($tenantAppPath);
        }

        $tenantAppPath = realpath($tenantAppPath);

        if ($tenantAppPath === false || ! is_dir($tenantAppPath)) {
            return null;
        }

        return $tenantAppPath;
    }
}
