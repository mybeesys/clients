<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'logo',
        'tax_name',
        'ceo_name',
        'country_id',
        'description',
        'name',
        'user_id',
        'name',
        'zipcode',
        'national_address',
        'country_id',
        'website',
        'state_id',
        'city_id'
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
        return $this->belongsTo(Country::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function subscriptios()
    {
        return $this->hasMany(Subscription::class);
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }
}
