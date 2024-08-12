<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TenantConnector
{

    public function reconnect($databaseName = null)
    {

        DB::purge('tenant_template');
        Config::set('database.connections.tenant_template.host', '127.0.0.1');
        Config::set('database.connections.tenant_template.port', '3306');
        Config::set('database.connections.tenant_template.database', $databaseName);
        Config::set('database.connections.tenant_template.username', 'root');
        Config::set('database.connections.tenant_template.password', '');
        DB::setDefaultConnection('tenant_template');
        session(['tenant' => $databaseName]);
    }
}
