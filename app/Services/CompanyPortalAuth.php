<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Main-system login rules:
 * - No company code → central (master) users only, for the clients admin panel.
 * - With a 6-digit company code → that company only, then hand off to its system.
 */
class CompanyPortalAuth
{
    /**
     * @return array{
     *     email: string,
     *     user: ?User,
     *     central_staff: bool,
     *     companies: Collection<int, object{
     *         company_id: int|null,
     *         company_name: string,
     *         tenant_id: string,
     *         domain: string,
     *         has_subscription: bool
     *     }>
     * }|null
     */
    public function attempt(string $identifier, string $password, ?string $companyCode = null): ?array
    {
        $identifier = trim($identifier);
        $companyCode = $this->normalizeCompanyCode($companyCode);

        if ($identifier === '' || $password === '') {
            return null;
        }

        if ($companyCode !== null) {
            return $this->fromCompanyCode($identifier, $password, $companyCode);
        }

        return $this->fromCentralStaff($identifier, $password);
    }

    public function normalizeCompanyCode(?string $companyCode): ?string
    {
        $companyCode = trim((string) $companyCode);

        if ($companyCode === '') {
            return null;
        }

        if (! preg_match('/^\d{6}$/', $companyCode)) {
            return null;
        }

        return $companyCode;
    }

    /**
     * @return array{
     *     email: string,
     *     user: User,
     *     central_staff: bool,
     *     companies: Collection<int, object>
     * }|null
     */
    protected function fromCentralStaff(string $identifier, string $password): ?array
    {
        $user = User::findByLoginIdentifier($identifier);

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return [
            'email' => strtolower(trim((string) $user->email)),
            'user' => $user,
            'central_staff' => true,
            'companies' => collect(),
        ];
    }

    /**
     * @return array{
     *     email: string,
     *     user: ?User,
     *     central_staff: bool,
     *     companies: Collection<int, object>
     * }|null
     */
    protected function fromCompanyCode(string $identifier, string $password, string $companyCode): ?array
    {
        $company = Company::query()
            ->with(['tenant.domains', 'subscription'])
            ->where('login_code', $companyCode)
            ->first();

        $tenant = $company?->tenant;
        $domain = $tenant?->domains?->first()?->domain;

        if ($company === null || $tenant === null || ! $domain || ! $this->tenantDatabaseReady($tenant)) {
            return null;
        }

        $employeeEmail = $this->authenticateTenantEmployee($tenant, $identifier, $password);

        if ($employeeEmail === null) {
            $central = $this->authenticateCentralMember($identifier, $password, $company);

            if ($central === null) {
                return null;
            }

            $employeeEmail = $central['email'];
            $user = $central['user'];
        } else {
            $user = User::query()
                ->where('email', $employeeEmail)
                ->whereNull('deleted_at')
                ->first();
        }

        return [
            'email' => $employeeEmail,
            'user' => $user,
            'central_staff' => false,
            'companies' => collect([(object) [
                'company_id' => $company->id,
                'company_name' => (string) $company->name,
                'tenant_id' => (string) $tenant->getTenantKey(),
                'domain' => (string) $domain,
                'has_subscription' => $company->subscription !== null,
            ]]),
        ];
    }

    protected function authenticateTenantEmployee(Tenant $tenant, string $identifier, string $password): ?string
    {
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        try {
            $employee = TenantContext::run($tenant, function () use ($identifier, $isEmail) {
                if (! Schema::hasTable('emp_employees')) {
                    return null;
                }

                $query = \Illuminate\Support\Facades\DB::table('emp_employees')
                    ->where('ems_access', true);

                if (Schema::hasColumn('emp_employees', 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }

                if ($isEmail) {
                    $query->where('email', $identifier);
                } else {
                    $query->where(function ($inner) use ($identifier) {
                        $inner->where('email', $identifier)
                            ->orWhere('user_name', $identifier);
                    });
                }

                return $query->first(['email', 'password']);
            });
        } catch (Throwable) {
            return null;
        }

        $hash = is_object($employee) ? (string) ($employee->password ?? '') : '';

        if ($employee === null || $hash === '' || ! Hash::check($password, $hash)) {
            return null;
        }

        return strtolower(trim((string) $employee->email));
    }

    /**
     * @return array{email: string, user: User}|null
     */
    protected function authenticateCentralMember(string $identifier, string $password, Company $company): ?array
    {
        $user = User::findByLoginIdentifier($identifier);

        if (! $user || ! Hash::check($password, $user->password) || ! filled($user->email)) {
            return null;
        }

        $canAccess = $user->accessibleCompanies()
            ->where('companies.id', $company->id)
            ->exists();

        if (! $canAccess) {
            return null;
        }

        return [
            'email' => strtolower(trim((string) $user->email)),
            'user' => $user,
        ];
    }

    protected function tenantDatabaseReady(Tenant $tenant): bool
    {
        try {
            return $tenant->database()->manager()->databaseExists(
                $tenant->database()->getName()
            );
        } catch (Throwable) {
            return false;
        }
    }
}
