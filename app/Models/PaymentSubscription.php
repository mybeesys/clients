<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSubscription extends Model
{
    use HasFactory, SoftDeletes;

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
