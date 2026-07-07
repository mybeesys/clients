<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProvisionTenantMemberEmployeeService
{
    public function provision(User $user, Company $company, string $role = 'member'): void
    {
        $tenant = $company->tenant;

        if ($tenant === null || ! filled($user->email) || $user->email === 'admin@admin.com') {
            return;
        }

        $tenant->run(function () use ($user, $role, $tenant) {
            $defaultEstId = DB::table('est_establishments')->whereNotNull('parent_id')->first()?->id;
            $emailLocalPart = Str::before($user->email, '@');

            DB::table('emp_employees')->updateOrInsert([
                'email' => $user->email,
            ], [
                'name' => Str::limit($user->name, 50, ''),
                'name_en' => Str::limit($emailLocalPart, 50, ''),
                'user_name' => Str::limit($emailLocalPart, 50, ''),
                'establishment_id' => $defaultEstId,
                'password' => $user->getAuthPassword(),
                'phone_number' => $tenant->owner_phone_number ?? null,
                'pin' => $user->pin,
                'ems_access' => true,
                'pos_is_active' => in_array($role, ['owner', 'admin'], true),
            ]);

            if (in_array($role, ['owner', 'admin'], true)) {
                app(GrantTenantAdminPermissionsService::class)->grantAdminEmployee($user->email);
            }
        });
    }
}
