<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Database\Factories\PaymentChannelFactory;

class PaymentChannel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'payment_channels';

    protected $fillable = ['title', 'status', 'class_name', 'currencies', 'image', 'settings', 'created_at'];

    public static $classes = [
        'Paypal',
        'MyFatoorah',
        'Pay360',
        'Fatora'
    ];
}
