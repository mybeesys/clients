<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyEntitlement extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id',
        'modules',
        'employees_quota',
        'establishments_quota',
        'screen_devices_quota',
        'period',
        'currency',
        'monthly_subtotal',
        'period_total',
        'line_items',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'modules' => 'array',
            'line_items' => 'array',
            'meta' => 'array',
            'monthly_subtotal' => 'decimal:2',
            'period_total' => 'decimal:2',
            'employees_quota' => 'integer',
            'establishments_quota' => 'integer',
            'screen_devices_quota' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function hasModule(string $key): bool
    {
        if ($key === 'platform') {
            return true;
        }

        return in_array($key, $this->modules ?? [], true);
    }

    /**
     * @return list<string>
     */
    public function allModuleKeys(): array
    {
        return array_values(array_unique(array_merge(['platform'], $this->modules ?? [])));
    }
}
