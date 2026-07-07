<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;

class CompanyMembershipService
{
    public const ROLES = [
        'owner' => 'owner',
        'admin' => 'admin',
        'member' => 'member',
    ];

    public function attach(User $user, Company $company, string $role = 'owner', bool $isPrimary = false): void
    {
        $user->companies()->syncWithoutDetaching([
            $company->id => [
                'role' => $role,
                'is_primary' => $isPrimary,
            ],
        ]);

        if ($isPrimary) {
            $this->clearOtherPrimaryFlags($user, $company->id);
        }
    }

    public function attachMember(User $user, Company $company, string $role = 'member', bool $isPrimary = false): void
    {
        $this->attach($user, $company, $role, $isPrimary);
        app(ProvisionTenantMemberEmployeeService::class)->provision($user, $company, $role);
    }

    public function attachOwner(User $user, Company $company): void
    {
        $hasPrimary = $user->companies()->wherePivot('is_primary', true)->exists();

        $this->attachMember($user, $company, 'owner', ! $hasPrimary);
    }

    public function updateMembership(Company $company, User $user, string $role, bool $isPrimary): void
    {
        $company->members()->updateExistingPivot($user->id, [
            'role' => $role,
            'is_primary' => $isPrimary,
        ]);

        if ($isPrimary) {
            $this->clearOtherPrimaryFlags($user, $company->id);
        }

        app(ProvisionTenantMemberEmployeeService::class)->provision($user, $company, $role);
    }

    public function detach(Company $company, User $user): void
    {
        $company->members()->detach($user->id);
    }

    protected function clearOtherPrimaryFlags(User $user, int $primaryCompanyId): void
    {
        \Illuminate\Support\Facades\DB::table('company_user')
            ->where('user_id', $user->id)
            ->where('company_id', '!=', $primaryCompanyId)
            ->update(['is_primary' => false]);
    }
}
