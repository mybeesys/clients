<?php

namespace App\Support;

use BadMethodCallException;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\PhpExecutableFinder;

class TenantProvisioner
{
    public static function start(string $tenantId): bool
    {
        if (! self::validTenantId($tenantId)) {
            return false;
        }

        if (self::processIsAlive($tenantId)) {
            return true;
        }

        try {
            Cache::forget('tenant-provisioning:'.$tenantId);
        } catch (BadMethodCallException) {
            //
        }

        if (! function_exists('exec') && PHP_OS_FAMILY !== 'Windows') {
            return false;
        }

        $php = (new PhpExecutableFinder())->find(false) ?: 'php';
        $artisan = base_path('artisan');
        self::pidDirectory();

        if (PHP_OS_FAMILY === 'Windows') {
            $command = 'start "" /B /D '.escapeshellarg(base_path()).' '
                .escapeshellarg($php).' '.escapeshellarg($artisan)
                .' tenants:provision '.escapeshellarg($tenantId);

            $handle = popen($command, 'r');

            if ($handle === false) {
                return false;
            }

            pclose($handle);

            return true;
        }

        $setsid = is_executable('/usr/bin/setsid') ? '/usr/bin/setsid' : 'setsid';
        $log = storage_path('logs/tenant-provision.log');
        $command = 'cd '.escapeshellarg(base_path())
            .' && '.$setsid.' '.escapeshellarg($php).' '.escapeshellarg($artisan)
            .' tenants:provision '.escapeshellarg($tenantId)
            .' >> '.escapeshellarg($log).' 2>&1 < /dev/null &';

        exec($command, $output, $exitCode);

        return $exitCode === 0;
    }

    public static function validTenantId(string $tenantId): bool
    {
        return (bool) preg_match('/^[a-z0-9]{1,64}$/', $tenantId);
    }

    public static function pidPath(string $tenantId): string
    {
        return self::pidDirectory().DIRECTORY_SEPARATOR.$tenantId.'.pid';
    }

    public static function processIsAlive(string $tenantId): bool
    {
        if (! self::validTenantId($tenantId)) {
            return false;
        }

        $path = self::pidPath($tenantId);

        if (! is_file($path)) {
            return false;
        }

        $pid = (int) trim((string) file_get_contents($path));

        if ($pid <= 0) {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            exec('tasklist /FI '.escapeshellarg('PID eq '.$pid).' /NH', $output);

            return isset($output[0]) && str_contains((string) $output[0], (string) $pid);
        }

        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0);
        }

        $output = [];
        exec('ps -p '.$pid.' -o pid=', $output);

        return isset($output[0]) && trim((string) $output[0]) !== '';
    }

    protected static function pidDirectory(): string
    {
        $directory = storage_path('framework/tenant-provision');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $directory;
    }
}
