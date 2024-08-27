<?php

namespace App\Providers;

use App\Support\TenantConnector;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Events\TenancyEnded;
use Stancl\Tenancy\Events\TenancyInitialized;

class TenantServiceProvider extends ServiceProvider
{
    use TenantConnector;

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
            $host = request()->getHost();
            $domain = Domain::where('domain', $host)->first();

            if ($domain) {
                $this->reconnect($domain->tenant->tenancy_db_name);
            }
        }
    }
}
