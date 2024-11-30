<?php

use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Database\Models\Domain;
if (!function_exists('getAllPaymentsHelper')) {
    function getAllPaymentsHelper()
    {
        $payments = [
            [
                'key' => 'Myfatoorah',
                'name' => 'Myfatoorah',
                'logo' => asset('assets/images/payment_methods/myfatoorah.png'),
                'fields' => ['PAYMENT_MYFATOORAH_SECRET_KEY', 'PAYMENT_COUNTRY_ISO', 'PAYMENT_TEST_MODE', 'PAYMENT_API_KEY'],
            ],
            [
                'key' => 'Paypal',
                'name' => 'Paypal',
                'logo' => asset('assets/images/payment_methods/paypal.png'),
                'fields' => ['PAYMENT_PAYPAL_SANDBOX_API_SECRET_KEY', 'PAYMENT_PAYPAL_SANDBOX_API_CLIENT_ID'],
            ],

            [
                'key' => 'Fatora',
                'name' => 'Fatora',
                'logo' => asset('assets/images/payment_methods/fatora.svg'),
                'fields' => ['PAYMENT_FATORA_APIKEY'],
            ],

            [
                'key' => 'Pay360',
                'name' => 'Pay360',
                'logo' => asset('assets/images/payment_methods/pay360.png'),
                'fields' => ['PAYMENT_PAY360_PASSWORD', 'PAYMENT_PAY360_USENAME', 'PAYMENT_PAY360_CASHIER_ID'],
            ],

        ];

        return $payments;
    }
}


if (!function_exists('getAllSMSHelper')) {
    function getAllSMSHelper()
    {
        $otps = [
            [
                'key' => 'Twilio',
                'name' => 'Twilio',
                'logo' => asset('assets/images/payment_methods/stripe.png'),
                'fields' => ['TWILIO_AUTH_TOKEN', 'TWILIO_VALID_TWILLO_NUMBER', 'TWILIO_SID'],
            ],

            [
                'key' => 'Nexmo',
                'name' => 'Nexmo',
                'logo' => asset('assets/images/payment_methods/myfatoorah.png'),
                'fields' => ['NEXMO_SECRET', 'NEXMO_KEY'],
            ],
            [
                'key' => 'SSLWireless',
                'name' => 'SSLWireless',
                'logo' => asset('assets/images/payment_methods/paypal.png'),
                'fields' => ['SSL_SMS_URL', 'SSL_SMS_SID', 'SSL_SMS_API_TOKEN'],
            ],

        ];

        return $otps;
    }
}

/* if (!function_exists('getAllDomains')) {
    function getAllDomains()
    {
        $domains = Domain::pluck('domain')->toArray();
        return $domains;
    }
} */
