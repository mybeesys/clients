<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;

class TenantApplicationUrl
{
    public static function forCompany(Company $company): ?string
    {
        $company->loadMissing(['tenant.domains', 'user.tenant.domains']);

        $domain = $company->tenant?->domains?->first()?->domain
            ?? $company->user?->tenant?->domains?->first()?->domain;

        if (! $domain) {
            return null;
        }

        return static::fromDomain($domain);
    }

    public static function forUser(User $user): ?string
    {
        $user->loadMissing('tenant.domains');

        $domain = $user->tenant?->domains?->first()?->domain;

        if (! $domain) {
            return null;
        }

        return static::fromDomain($domain);
    }

    public static function fromDomain(string $domain): string
    {
        $protocol = config('services.tenant_app.protocol')
            ?? (request()->secure() ? 'https' : 'http');
        $protocol = str_ends_with($protocol, '://')
            ? $protocol
            : $protocol.'://';

        $port = config('services.tenant_app.port');

        if ($port && ! str_contains($domain, ':')) {
            return $protocol.$domain.':'.$port;
        }

        return $protocol.$domain;
    }
}
