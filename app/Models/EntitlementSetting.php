<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EntitlementSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('entitlement_catalog_settings'));
        static::deleted(fn () => Cache::forget('entitlement_catalog_settings'));
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $all = Cache::remember('entitlement_catalog_settings', now()->addMinutes(30), function () {
            return static::query()->pluck('value', 'key')->all();
        });

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        $value = $all[$key];

        if (! is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);
        if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_scalar($value) || $value === null ? $value : json_encode($value, JSON_UNESCAPED_UNICODE)]
        );

        Cache::forget('entitlement_catalog_settings');
    }
}
