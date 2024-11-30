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
    ];

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'coupons_plans');
    }
}

