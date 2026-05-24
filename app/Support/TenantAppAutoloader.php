<?php

namespace App\Support;

class TenantAppAutoloader
{
    public static function register(?string $tenantAppPath = null): void
    {
        $tenantAppPath = $tenantAppPath ?? env('TENANT_APP_PATH', '../mybeeCompany');

        if (! is_string($tenantAppPath) || $tenantAppPath === '') {
            return;
        }

        if (! str_starts_with($tenantAppPath, DIRECTORY_SEPARATOR) && ! preg_match('#^[A-Za-z]:\\\\#', $tenantAppPath)) {
            $tenantAppPath = base_path($tenantAppPath);
        }

        $tenantAppPath = realpath($tenantAppPath);

        if ($tenantAppPath === false) {
            return;
        }

        $vendorAutoload = $tenantAppPath.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

        if (is_file($vendorAutoload)) {
            require_once $vendorAutoload;

            return;
        }

        $modulesPath = $tenantAppPath.DIRECTORY_SEPARATOR.'Modules';

        if (! is_dir($modulesPath)) {
            return;
        }

        spl_autoload_register(static function (string $class) use ($modulesPath): void {
            if (! str_starts_with($class, 'Modules\\')) {
                return;
            }

            if (! preg_match('/^Modules\\\\([^\\\\]+)\\\\(.+)$/', $class, $matches)) {
                return;
            }

            $file = $modulesPath
                .DIRECTORY_SEPARATOR.$matches[1]
                .DIRECTORY_SEPARATOR.'app'
                .DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $matches[2])
                .'.php';

            if (is_file($file)) {
                require_once $file;
            }
        });
    }
}
