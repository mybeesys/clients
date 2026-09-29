<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EntitlementProduct extends Model
{
    public const TYPE_PLATFORM = 'platform';

    public const TYPE_MODULE = 'module';

    public const TYPE_QUOTA = 'quota';

    protected $fillable = [
        'key',
        'type',
        'group',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'price_month',
        'price_per_extra_month',
        'included',
        'min',
        'max',
        'requires',
        'linked_module',
        'icon',
        'sort_order',
        'active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'price_month' => 'decimal:2',
            'price_per_extra_month' => 'decimal:2',
            'included' => 'integer',
            'min' => 'integer',
            'max' => 'integer',
            'requires' => 'array',
            'meta' => 'array',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('entitlement_catalog_products'));
        static::deleted(fn () => Cache::forget('entitlement_catalog_products'));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function toCatalogArray(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'group' => $this->group,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'price_month' => (float) $this->price_month,
            'price_per_extra_month' => $this->price_per_extra_month !== null
                ? (float) $this->price_per_extra_month
                : null,
            'included' => $this->included,
            'min' => $this->min,
            'max' => $this->max,
            'requires' => array_values($this->requires ?? []),
            'requires_any' => array_values($this->meta['requires_any'] ?? []),
            'linked_module' => $this->linked_module,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            'meta' => $this->meta ?? [],
        ];
    }
}
