<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\GrantTenantAdminPermissionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class GrantTenantAdminPermissionsController extends Controller
{
    public function __invoke(
        Request $request,
        string $tenant,
        GrantTenantAdminPermissionsService $grantService,
    ): JsonResponse {
        $secret = (string) config('tenant-app.admin_grant_secret');
        if ($secret === '' || ! hash_equals($secret, (string) $request->query('secret', ''))) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $tenantModel = Tenant::query()->find($tenant);
        if ($tenantModel === null) {
            return response()->json(['message' => 'Tenant not found.'], 404);
        }

        try {
            $result = $grantService->grantForTenant(
                $tenantModel,
                $request->query('email'),
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok' => true,
            'tenant_id' => $tenantModel->getTenantKey(),
            ...$result,
        ]);
    }
}
