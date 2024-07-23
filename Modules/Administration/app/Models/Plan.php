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
    protected $fillable = ['name', 'description', 'price', 'duration', 'active'];

    public function payments()
    {
        return $this->hasMany(PaymentSubscription::class);
    }


    public function scopeActive($q)
    {
        return $q->where('is_active', 1);
    }
}
