<?php

namespace App\Support;

/**
 * Builds the one-time signed URL that opens a company session from the main system.
 * Reuses the existing tenant-switch token — no database tables are added.
 */
class CompanyPortalHandoff
{
    public static function url(string $email, string $tenantId, string $domain): string
    {
        $token = TenantSwitchToken::issue(
            strtolower(trim($email)),
            $tenantId,
        );

        return TenantApplicationUrl::fromDomain($domain)
            .'/auth/tenant-switch?token='.urlencode($token);
    }
}
