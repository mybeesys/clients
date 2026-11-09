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

        // Never load mybeeCompany/vendor/autoload.php here — it replaces Symfony/Laravel
        // classes (e.g. NullOutput::VERBOSITY_SILENT) and breaks Artisan during company create.

        $modulesPath = $tenantAppPath.DIRECTORY_SEPARATOR.'Modules';

        if (! is_dir($modulesPath)) {
            return;
        }

        static $registered = false;

        if ($registered) {
            return;
        }

        $registered = true;

        spl_autoload_register(static function (string $class) use ($modulesPath): void {
            if (! str_starts_with($class, 'Modules\\')) {
                return;
            }

            if (! preg_match('/^Modules\\\\([^\\\\]+)\\\\(.+)$/', $class, $matches)) {
                return;
            }

            $relative = str_replace('\\', DIRECTORY_SEPARATOR, $matches[2]).'.php';
            $moduleRoot = $modulesPath.DIRECTORY_SEPARATOR.$matches[1].DIRECTORY_SEPARATOR;

            // composer.json maps "Modules\\" to "Modules/", so
            // Modules\Franchise\Support\FranchisePermissions is
            // Modules/Franchise/Support/FranchisePermissions.php.
            // Some modules also keep classes under app/.
            foreach ([$moduleRoot.$relative, $moduleRoot.'app'.DIRECTORY_SEPARATOR.$relative] as $file) {
                if (is_file($file)) {
                    require_once $file;

                    return;
                }
            }
        });
    }
}
