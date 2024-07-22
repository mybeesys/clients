<?php

use Modules\Administration\Models\Feature;
use Modules\Administration\Models\FeaturePlan;
use Modules\Administration\Models\Plan;

return [
    'database' => [
        'cancel_migrations_autoloading' => false,
    ],

    'feature_tickets' => env('SOULBSCRIPTION_FEATURE_TICKETS', false),

    'models' => [

        'feature' => Feature::class,

        'feature_consumption' => \LucasDotVin\Soulbscription\Models\FeatureConsumption::class,

        'feature_ticket' => \LucasDotVin\Soulbscription\Models\FeatureTicket::class,

        'feature_plan' => FeaturePlan::class,

        'plan' => Plan::class,

        'subscriber' => [
            'uses_uuid' => env('SOULBSCRIPTION_SUBSCRIBER_USES_UUID', false),
        ],

        'subscription' => \LucasDotVin\Soulbscription\Models\Subscription::class,

        'subscription_renewal' => \LucasDotVin\Soulbscription\Models\SubscriptionRenewal::class,
    ],
];
