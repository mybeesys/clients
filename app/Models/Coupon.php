<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'active' => 'boolean',
        'start' => 'date',
        'end' => 'date',
        'value' => 'float',
        'max_discount' => 'float',
        'apply_months' => 'integer',
        'uses_limit' => 'integer',
        'uses_count' => 'integer',
    ];

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'coupons_plans');
    }

    public function isCustomized(): bool
    {
        return ($this->apply_mode && $this->apply_mode !== 'full')
            || ($this->max_discount !== null && (float) $this->max_discount > 0)
            || filled($this->applicable_period);
    }
}
