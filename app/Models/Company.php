<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Company\Database\Factories\CompanyFactory;
use LucasDotVin\Soulbscription\Models\Concerns\HasSubscriptions;
use Modules\Administration\Models\PaymentSubscription;
use Modules\Company\Models\Contact;

class Company extends Model
{
    use HasFactory, HasSubscriptions;

    protected $table = "companies";
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'logo', 'tax_name',
        'ceo_name', 'country_id',
        'description', 'name',
        'user_id', 'name', 'zipcode',
        'national_address',
        'country_id', 'website', 'state_id', 'city_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentSubscription::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
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
}
