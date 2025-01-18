<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use LucasDotVin\Soulbscription\Models\Plan as ModelsPlan;


class Plan extends ModelsPlan
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'plans';

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'periodicity',
        'periodicity_type',
        'discount',
        'price',
        'price_after_discount',
        'discount_period_amount_type',
        'discount_type',
        'grace_days',
        'active',
        'price'
    ];

    // protected function casts(): array
    // {
    //     return [
    //         'specifications' => 'array',
    //     ];
    // }

    public function payments()
    {
        return $this->hasMany(PaymentSubscription::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupons_plans');
    }

    public function scopeActive($q)
    {
        return $q->where('active', 1);
    }

    public function feature_plans()
    {
        return $this->hasMany(FeaturePlan::class);
    }
}
