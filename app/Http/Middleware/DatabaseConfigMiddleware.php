<?php

namespace App\Http\Middleware;

use App\Support\TenantConnector;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Stancl\Tenancy\Database\Models\Domain;
use Symfony\Component\HttpFoundation\Response;

class DatabaseConfigMiddleware
{
    use TenantConnector;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Schema::hasTable('domains')) {
            $host = $request->getHost();
            $domain = Domain::where('domain', $host)->first();

            if ($domain) {
                $this->reconnect($domain->tenant_id . '_db');
                $request->session()->put('tenant', $domain->tenant_id . '_db');
            }
        }

        return $next($request);
    }
}
