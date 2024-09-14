<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Database\Factories\CouponPlanFactory;

class CouponPlan extends Model
{
    use HasFactory;
    protected $table = 'coupon_plan';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['plan_id', 'coupon_id'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
