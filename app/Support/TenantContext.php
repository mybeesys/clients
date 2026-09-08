<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Safely run central-panel work against a tenant DB without leaving
 * Filament/Livewire on a purged `tenant` connection afterward.
 *
 * Stancl's Tenant::run() does not use try/finally; an exception inside the
 * callback leaves tenancy initialized. Even on success, Livewire re-renders
 * can still see a stale default connection name of `tenant` after purge.
 */
final class TenantContext
{
    public static function run(Tenant $tenant, callable $callback): mixed
    {
        $central = self::centralConnectionName();
        $originalDefault = config('database.default');

        try {
            return $tenant->run($callback);
        } finally {
            self::ensureCentral(
                is_string($originalDefault) ? $originalDefault : null,
                $central,
            );
        }
    }

    public static function ensureCentral(?string $originalDefault = null, ?string $central = null): void
    {
        $central ??= self::centralConnectionName();

        try {
            if (function_exists('tenancy') && tenancy()->initialized) {
                tenancy()->end();
            }
        } catch (Throwable) {
            // Still force the central connection below.
        }

        $default = self::resolveUsableConnection($originalDefault) ?? $central;

        config(['database.default' => $default]);

        $connections = config('database.connections', []);
        unset($connections['tenant']);
        config(['database.connections' => $connections]);

        try {
            DB::purge('tenant');
        } catch (Throwable) {
            // Already purged.
        }

        DB::setDefaultConnection($default);
    }

    public static function centralConnectionName(): string
    {
        $candidates = [
            config('tenancy.database.central_connection'),
            config('database.default'),
            env('DB_CONNECTION'),
            'mysql',
        ];

        foreach ($candidates as $candidate) {
            $resolved = self::resolveUsableConnection(is_string($candidate) ? $candidate : null);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return 'mysql';
    }

    private static function resolveUsableConnection(?string $name): ?string
    {
        if (! is_string($name) || $name === '' || $name === 'tenant') {
            return null;
        }

        return config("database.connections.{$name}") ? $name : null;
    }
}
