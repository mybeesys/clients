<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Database\Models\Domain;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('domains')) {
            $domain = Domain::where('domain', request()->getHost())->first();
            if ($domain) {
                DB::purge('mysql');

                config([
                    'database.connections.mysql.database' => $domain->tenant_id . '_db',
                    'database.connections.mysql.username' => 'root',
                    'database.connections.mysql.password' => '',
                ]);


                DB::reconnect('mysql');
            }
        }
    }
}
