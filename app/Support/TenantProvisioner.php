<?php

namespace App\Support;

use App\Jobs\ProvisionTenantDatabase;
use Illuminate\Support\Facades\Log;
use Throwable;

use function Illuminate\Support\php_binary;

/**
 * Starts tenant database setup in a separate PHP process so the browser
 * receives the registration response without waiting for migrations.
 */
class TenantProvisioner
{
    public static function start(string $tenantId): void
    {
        if ($tenantId === '') {
            return;
        }

        if (self::spawn($tenantId)) {
            return;
        }

        dispatch(new ProvisionTenantDatabase($tenantId))->afterResponse();
    }

    protected static function spawn(string $tenantId): bool
    {
        $function = PHP_OS_FAMILY === 'Windows' ? 'popen' : 'exec';
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        if (! function_exists($function) || in_array($function, $disabled, true)) {
            return false;
        }

        $php = php_binary();
        $artisan = base_path('artisan');
        $log = storage_path('logs/tenant-provision.log');

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $command = 'start /B "" '.escapeshellarg($php).' '.escapeshellarg($artisan).' tenants:provision '.escapeshellarg($tenantId);
                $handle = popen($command, 'r');

                if ($handle === false) {
                    return false;
                }

                pclose($handle);

                return true;
            }

            $command = 'nohup '.escapeshellarg($php).' '.escapeshellarg($artisan).' tenants:provision '.escapeshellarg($tenantId)
                .' >> '.escapeshellarg($log).' 2>&1 &';
            exec($command, $output, $exitCode);

            return $exitCode === 0;
        } catch (Throwable $exception) {
            Log::warning('Could not start tenant provisioning in the background.', [
                'tenant' => $tenantId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
