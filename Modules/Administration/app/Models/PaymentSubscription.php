<?php

namespace Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Database\Factories\PaymentSubscriptionFactory;
use Modules\Company\Models\Company;

class PaymentSubscription extends Model
{
    use HasFactory;


    protected $table = 'payment_subscription';
    protected $fillable = ['id', 'subscription_id', 'amount', 'payment_date', 'payment_method', 'transaction_id', 'company_id', 'plan_id', 'remaining_amount', 'paid_amount'];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
