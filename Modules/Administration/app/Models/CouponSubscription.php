<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Database\Factories\CouponSubscriptionFactory;

class CouponSubscription extends Model
{
    use HasFactory;

    protected $table = 'coupon_subscription';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['subscription_id', 'coupon_id'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
