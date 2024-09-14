<?php

namespace Modules\Administration\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LucasDotVin\Soulbscription\Models\Subscription as ModelsSubscription;
use Modules\Administration\Database\Factories\SubscriptionFactory;
use Modules\Company\Models\Tenant;

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
        'plan_id',
        'company_id',
        'tenant_id', 'subdomain'

    ];


    public function payments()
    {
        return $this->hasMany(PaymentSubscription::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
