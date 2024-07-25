<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Administration\Events\TenantCreated;
use Modules\Administration\Models\Subscription;
use Modules\Company\Models\Tenant;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {

        Route::get('/', function () {
            // $tenant = Tenant::where('id', 'momo-202407251721911166')->first();
            // config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);
            // DB::purge('mysql');
            // DB::reconnect('mysql');
            // $rr = event(new TenantCreated($tenant));

            return view('welcome');
        });
    });
}
