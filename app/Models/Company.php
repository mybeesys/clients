<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'logo',
        'tax_name',
        'ceo_name',
        'description',
        'user_id',
        'zipcode',
        'national_address',
        'country_id',
        'website',
        'state',
        'city'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentSubscription::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }
}
