<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotVin\Soulbscription\Models\Subscription as ModelsSubscription;

class Subscription extends ModelsSubscription
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'canceled_at',
        'expired_at',
        'grace_days_ended_at',
        'started_at',
        'suppressed_at',
        'was_switched',
        'plan_id',
        'subscriber_id',
        'subscriber_type',
    ];

    public function payments()
    {
        return $this->hasMany(PaymentSubscription::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
