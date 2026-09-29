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

        return $all[$key] ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_scalar($value) || $value === null ? $value : json_encode($value)]
        );
    }
}
