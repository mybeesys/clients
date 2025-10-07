<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'is_company',
        'pin',
        'password',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }

    public function is_company()
    {
        return $this->is_company;
    }

    public function scopeIsNotCompany($query)
    {
        return $query->where('is_company', 0);
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }


    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($user) {
    //          do {
    //             $code = strtoupper(Str::random(6));
    //         } while (self::where('pin', $code)->exists());

    //         $user->code = $code;
    //     });
    // }
}
