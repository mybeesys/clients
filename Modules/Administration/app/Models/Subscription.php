<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LucasDotVin\Soulbscription\Models\Subscription as ModelsSubscription;
use Modules\Administration\Database\Factories\SubscriptionFactory;

class Subscription extends ModelsSubscription
{
    use HasFactory;

    protected $fillable = [
        'canceled_at',
        'expired_at',
        'grace_days_ended_at',
        'started_at',
        'suppressed_at',
        'was_switched',
    ];

   
    public function payments()
    {
        return $this->hasMany(PaymentSubscription::class);
    }


}
