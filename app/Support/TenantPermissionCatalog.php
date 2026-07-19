<?php

namespace App\Support;

class TenantPermissionCatalog
{
    /**
     * Load permission definitions from the tenant app and/or bundled fallbacks.
     *
     * @return array{permissions: list<array>, sources: list<string>, tried: list<string>}
     */
    public static function load(): array
    {
        $permissions = [];
        $sources = [];
        $tried = [];

        foreach (static::candidateFiles() as $file) {
            $tried[] = $file;

            if (! is_file($file)) {
                continue;
            }

            $loaded = include $file;
            if (! is_array($loaded) || $loaded === []) {
                continue;
            }

            $permissions = array_merge($permissions, $loaded);
            $sources[] = $file;
        }

        return [
            'permissions' => $permissions,
            'sources' => $sources,
            'tried' => $tried,
        ];
    }

    /**
     * @return list<string>
     */
    public static function candidateFiles(): array
    {
        $relativePaths = config('tenant-app.permission_data_paths', [
            'Modules/Employee/data/pos-permissions.php',
            'Modules/Employee/data/dashboard-permissions.php',
        ]);

        $roots = static::roots();
        $files = [];

        foreach ($roots as $root) {
            foreach ($relativePaths as $relativePath) {
                $files[] = rtrim($root, '/\\').DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            }
        }

        // Bundled copies inside the central app (production-safe fallback).
        foreach (config('tenant-app.bundled_permission_data_paths', []) as $relativePath) {
            $files[] = base_path(str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        }

        return array_values(array_unique($files));
    }

    /**
     * @return list<string>
     */
    public static function roots(): array
    {
        $configured = (string) config('tenant-app.path', env('TENANT_APP_PATH', '../my-bee-company'));
        $candidates = [
            $configured,
            '../my-bee-company',
            '../mybeeCompany',
            '../my-bee-Company',
        ];

        $roots = [];

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            $path = $candidate;
            if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('#^[A-Za-z]:[\\\\/]#', $path)) {
                $path = base_path($path);
            }

            $resolved = realpath($path);
            if ($resolved !== false) {
                $roots[] = $resolved;
            }
        }

        return array_values(array_unique($roots));
    }
}
