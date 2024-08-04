<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TenantConnector
{

    public function reconnect($databaseName = null)
    {

        DB::purge('tenant');
        Config::set('database.connections.tenant.host', '127.0.0.1');
        Config::set('database.connections.tenant.port', '3306');
        Config::set('database.connections.tenant.database', $databaseName);
        Config::set('database.connections.tenant.username', 'root');
        Config::set('database.connections.tenant.password', '');
        DB::setDefaultConnection('tenant');
        session(['tenant' => $databaseName]);
    }
}
