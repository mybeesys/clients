<?php

namespace Modules\Administration\Listeners;

use Illuminate\Support\Facades\Artisan;
use Modules\Administration\Events\TenantCreated;

class SeedTenantDatabase
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TenantCreated $event)
    {
        tenancy()->initialize($event->tenant);

        Artisan::call('tenants:seed', [
            '--class' => 'Modules\\Administration\\Database\\Seeders\\Tenant\\DatabaseSeeder',
        ]);
    }
}
