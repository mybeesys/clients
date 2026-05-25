<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;

class TenantApplicationUrl
{
    public static function forCompany(Company $company): ?string
    {
        $company->loadMissing('user.tenant.domains');

        return $company->user ? static::forUser($company->user) : null;
    }

    public static function forUser(User $user): ?string    {
        $domain = $user->tenant?->domains?->first()?->domain;

        if (! $domain) {
            return null;
        }

        $protocol = request()->secure() ? 'https://' : 'http://';

        $port = config('services.tenant_app.port');

        if ($port && ! str_contains($domain, ':')) {
            return $protocol.$domain.':'.$port;
        }

        return $protocol.$domain;
    }
}
