<?php

namespace App\Services;

use App\Models\Tenant;
use App\Support\TenantAppAutoloader;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GrantTenantAdminPermissionsService
{
    public function grantForTenant(Tenant $tenant, ?string $employeeEmail = null): array
    {
        TenantAppAutoloader::register();

        return $tenant->run(function () use ($employeeEmail) {
            $this->ensurePermissionsExist();

            return $this->grantAdminEmployee($employeeEmail ?? 'admin@admin.com');
        });
    }

    public function grantAdminEmployee(string $email): array
    {
        $employee = DB::table('emp_employees')->where('email', $email)->first();

        if ($employee === null) {
            throw new RuntimeException("Employee not found: {$email}");
        }

        $employeeId = (int) $employee->id;

        DB::table('emp_employees')->where('id', $employeeId)->update([
            'ems_access' => true,
            'pos_is_active' => true,
        ]);

        $emsGranted = $this->grantEmsAllPermissions($employeeId);
        $posGranted = $this->grantPosAllPermissions($employeeId);

        return [
            'employee_id' => $employeeId,
            'employee_email' => $email,
            'ems_permissions_granted' => $emsGranted,
            'pos_permissions_granted' => $posGranted,
        ];
    }

    public function grantEmsAllPermissions(int $employeeId): int
    {
        $permissions = DB::table('permissions')
            ->where('name', 'LIKE', '%all%')
            ->where('type', 'ems')
            ->pluck('id');

        $granted = 0;

        foreach ($permissions as $permissionId) {
            $inserted = DB::table('model_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionId,
                'model_type' => 'Modules\Employee\Models\Employee',
                'model_id' => $employeeId,
            ]);

            if ($inserted) {
                $granted++;
            }
        }

        return $granted;
    }

    public function grantPosAllPermissions(int $employeeId): int
    {
        $permissionNames = [
            'select_all_permissions',
            'owner_access',
            'manager_access',
        ];

        $permissions = DB::table('permissions')
            ->where('type', 'pos')
            ->whereIn('name', $permissionNames)
            ->pluck('id');

        $granted = 0;

        foreach ($permissions as $permissionId) {
            $inserted = DB::table('model_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionId,
                'model_type' => 'Modules\Employee\Models\Employee',
                'model_id' => $employeeId,
            ]);

            if ($inserted) {
                $granted++;
            }
        }

        return $granted;
    }

    private function ensurePermissionsExist(): void
    {
        if (DB::table('permissions')->exists()) {
            return;
        }

        $tenantAppPath = rtrim(config('tenant-app.path'), '/\\');
        $permissions = [];

        foreach (config('tenant-app.permission_data_paths', []) as $relativePath) {
            $file = $tenantAppPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (! is_file($file)) {
                continue;
            }

            $permissions = array_merge($permissions, include $file);
        }

        if ($permissions === []) {
            throw new RuntimeException('No tenant permission files found. Check TENANT_APP_PATH.');
        }

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert([
                'name' => $permission['name'],
            ], [
                'type' => $permission['type'],
                'name_ar' => $permission['name_ar'],
                'description' => $permission['description'],
                'description_ar' => $permission['description_ar'],
                'guard_name' => 'web',
            ]);
        }
    }
}
