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

    protected $fillable = ['name', 'name_ar', 'description', 'description_ar', 'specifications', 'grace_days', 'active', 'price'];

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
        ];
    }


    public function features()
    {
        return $this->belongsToMany(config('soulbscription.models.feature'))
            ->using(config('soulbscription.models.feature_plan'))
            ->withPivot(['amount', 'charges'])->withTimestamps();
    }

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
