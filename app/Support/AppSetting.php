<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppSetting
{
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            if (function_exists('setting')) {
                $value = setting($key, $default);

                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        } catch (\Throwable) {
            // Fall through to the settings table.
        }

        try {
            $table = (string) config('settings.database_table_name', 'settings');

            if (! Schema::hasTable($table)) {
                return $default;
            }

            $raw = DB::table($table)->where('key', $key)->value('value');

            if ($raw === null) {
                return $default;
            }

            if (is_string($raw)) {
                $decoded = json_decode($raw, true);

                return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
            }

            return $raw;
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        try {
            if (function_exists('setting')) {
                setting([$key => $value]);

                return;
            }
        } catch (\Throwable) {
            //
        }

        $table = (string) config('settings.database_table_name', 'settings');

        DB::table($table)->updateOrInsert(
            ['key' => $key],
            [
                'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }
}
