<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LucasDotVin\Soulbscription\Models\Plan as ModelsPlan;
use Modules\Administration\Database\Factories\PlanFactory;

class Plan extends ModelsPlan
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'plans';

    protected $fillable = ['name', 'description', 'price', 'duration', 'active'];

    public function features()
    {
        return $this->belongsToMany(config('soulbscription.models.feature'))
            ->using(config('soulbscription.models.feature_plan'))
            ->withPivot(['charges', 'value']);
    }

    public function payments()
    {
        return $this->hasMany(PaymentSubscription::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_plan');
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
