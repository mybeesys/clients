<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Database\Factories\CouponFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $table = 'coupons';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['expired_at', 'active', 'max_use', 'status', 'descount_type', 'description', 'name', 'amount'];

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'coupon_plan');
    }

    public function coupon_subscriptions()
    {
        return $this->hasMany(CouponSubscription::class);
    }

}
