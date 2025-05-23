<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotVin\Soulbscription\Models\Concerns\HasSubscriptions;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasSubscriptions;

    protected $fillable = [
        'name',
        'business_type',
        'logo',
        'tax_name',
        'tax_number',
        'ceo_name',
        'description',
        'phone',
        'user_id',
        'zipcode',
        'national_address',
        'country_id',
        'website',
        'state',
        'city',
        'phone'
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

    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }
}
