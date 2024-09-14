<?php

namespace Modules\Administration\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Administration\Events\SubscriptionHandled;
use Modules\Administration\Events\TenantCreated;
use Modules\Administration\Listeners\SeedTenantDatabase;
use Modules\Administration\Listeners\SendSubscriptionEmail;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        
        SubscriptionHandled::class => [
            SendSubscriptionEmail::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     *
     * @return void
     */
    protected function configureEmailVerification(): void
    {
    }
}
