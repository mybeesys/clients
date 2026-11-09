<?php

namespace App\Support;

class TenantDomainName
{
    public static function forTenantKey(string $tenantKey): string
    {
        $host = preg_replace('#^https?://#i', '', (string) config('app.url')) ?? '';
        $host = explode('/', $host)[0];

        return $tenantKey.'.'.trim($host);
    }
}
