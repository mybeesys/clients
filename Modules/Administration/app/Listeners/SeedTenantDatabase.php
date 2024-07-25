<?php

namespace Modules\Administration\Listeners;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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

        config(['database.connections.tenant.database' => $event->tenant->tenancy_db_name]);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        Artisan::call('tenants:seed', [
            '--class' => 'Modules\\Administration\\Database\\Seeders\\Tenant\\DatabaseSeeder',
        ]);
    }
}
