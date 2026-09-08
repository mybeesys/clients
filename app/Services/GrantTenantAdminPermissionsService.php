<?php

namespace App\Services;

use App\Models\Tenant;
use App\Support\TenantAppAutoloader;
use App\Support\TenantContext;
use App\Support\TenantPermissionCatalog;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GrantTenantAdminPermissionsService
{
    private const MODEL_TYPE = 'Modules\Employee\Models\Employee';

    public function grantForTenant(Tenant $tenant, ?string $employeeEmail = null): array
    {
        TenantAppAutoloader::register();

        return TenantContext::run($tenant, function () use ($employeeEmail) {
            $catalog = $this->ensurePermissionsCatalog();

            return array_merge(
                $this->grantAdminEmployee($employeeEmail ?? 'admin@admin.com'),
                ['permissions_catalog_synced' => $catalog],
            );
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

        $ems = $this->grantPermissionsOfType($employeeId, 'ems');
        $pos = $this->grantPermissionsOfType($employeeId, 'pos');

        $this->forgetPermissionCache($employeeId);

        return [
            'employee_id' => $employeeId,
            'employee_email' => $email,
            'ems_permissions_available' => $ems['available'],
            'ems_permissions_newly_granted' => $ems['newly_granted'],
            'ems_permissions_total' => $ems['total_attached'],
            'pos_permissions_available' => $pos['available'],
            'pos_permissions_newly_granted' => $pos['newly_granted'],
            'pos_permissions_total' => $pos['total_attached'],
            'ems_permissions_granted' => $ems['total_attached'],
            'pos_permissions_granted' => $pos['total_attached'],
        ];
    }

    public function grantEmsAllPermissions(int $employeeId): int
    {
        return $this->grantPermissionsOfType($employeeId, 'ems')['total_attached'];
    }

    public function grantPosAllPermissions(int $employeeId): int
    {
        return $this->grantPermissionsOfType($employeeId, 'pos')['total_attached'];
    }

    /**
     * @return array{available: int, newly_granted: int, total_attached: int}
     */
    private function grantPermissionsOfType(int $employeeId, string $type): array
    {
        $permissionIds = DB::table('permissions')
            ->where('type', $type)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $available = count($permissionIds);
        $newlyGranted = 0;

        foreach ($permissionIds as $permissionId) {
            $inserted = DB::table('model_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionId,
                'model_type' => self::MODEL_TYPE,
                'model_id' => $employeeId,
            ]);

            if ((int) $inserted > 0) {
                $newlyGranted++;
            }
        }

        $totalAttached = (int) DB::table('model_has_permissions')
            ->where('model_type', self::MODEL_TYPE)
            ->where('model_id', $employeeId)
            ->whereIn('permission_id', $permissionIds ?: [0])
            ->count();

        return [
            'available' => $available,
            'newly_granted' => $newlyGranted,
            'total_attached' => $totalAttached,
        ];
    }

    /**
     * @return array{synced: int, ems: int, pos: int, sources: list<string>, tried: list<string>}
     */
    private function ensurePermissionsCatalog(): array
    {
        $loaded = TenantPermissionCatalog::load();
        $permissions = $loaded['permissions'];

        if ($permissions === []) {
            if (! DB::table('permissions')->exists()) {
                throw new RuntimeException(
                    'No tenant permission files found and permissions table is empty. '.
                    'Deploy database/tenant-permissions/* or fix TENANT_APP_PATH. Tried: '.
                    implode(' | ', $loaded['tried'])
                );
            }

            return [
                'synced' => 0,
                'ems' => (int) DB::table('permissions')->where('type', 'ems')->count(),
                'pos' => (int) DB::table('permissions')->where('type', 'pos')->count(),
                'sources' => $loaded['sources'],
                'tried' => $loaded['tried'],
            ];
        }

        $synced = 0;

        foreach ($permissions as $permission) {
            if (! is_array($permission) || empty($permission['name'])) {
                continue;
            }

            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [
                    'type' => $permission['type'] ?? null,
                    'name_ar' => $permission['name_ar'] ?? null,
                    'description' => $permission['description'] ?? null,
                    'description_ar' => $permission['description_ar'] ?? null,
                    'guard_name' => 'web',
                ]
            );
            $synced++;
        }

        return [
            'synced' => $synced,
            'ems' => (int) DB::table('permissions')->where('type', 'ems')->count(),
            'pos' => (int) DB::table('permissions')->where('type', 'pos')->count(),
            'sources' => $loaded['sources'],
            'tried' => $loaded['tried'],
        ];
    }

    private function forgetPermissionCache(int $employeeId): void
    {
        try {
            if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            }
        } catch (\Throwable) {
            // ignore
        }

        try {
            if (DB::getSchemaBuilder()->hasTable('cache')) {
                DB::table('cache')->where('key', 'like', '%spatie.permission.cache%')->delete();
            }
        } catch (\Throwable) {
            // ignore
        }
    }
}
