<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Support\TenantApplicationUrl;
use App\Support\TenantProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class RegistrationProgressController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        if ($this->tenant() === null) {
            return redirect()->route('filament.admin.auth.login');
        }

        return response()
            ->view('auth.registration-progress', $this->viewData())
            ->header('Cache-Control', 'no-store');
    }

    public function status(): JsonResponse
    {
        $state = $this->viewData();

        if ($state['status'] === 'missing') {
            return response()->json($state, 404);
        }

        return response()->json([
            'status' => $state['status'],
            'tenant_url' => $state['tenantUrl'],
        ])->header('Cache-Control', 'no-store');
    }

    public function retry(): RedirectResponse
    {
        $tenant = $this->tenant();

        if ($tenant === null) {
            return redirect()->route('filament.admin.auth.login');
        }

        if ((string) $tenant->provision_status !== 'ready') {
            $tenant->provision_status = 'queued';
            $tenant->provision_error = null;
            $tenant->save();
            TenantProvisioner::start((string) $tenant->getTenantKey());
        }

        return redirect()->route('register.pending');
    }

    /**
     * @return array{status: string, tenantUrl: ?string, companyName: ?string, email: ?string}
     */
    protected function viewData(): array
    {
        $pending = session('registration.pending');
        $tenant = $this->tenant();

        if (! is_array($pending) || $tenant === null) {
            return [
                'status' => 'missing',
                'tenantUrl' => null,
                'companyName' => null,
                'email' => null,
            ];
        }

        $tenant->loadMissing('domains');
        $domain = $tenant->domains->first()?->domain;
        $status = (string) ($tenant->provision_status ?: 'queued');
        $url = is_string($domain) && $domain !== ''
            ? TenantApplicationUrl::fromDomain($domain)
            : null;

        if ($status === 'ready' && $url !== null) {
            $resolved = 'ready';
        } elseif ($status === 'failed') {
            $resolved = 'failed';
        } else {
            $resolved = 'pending';
        }

        return [
            'status' => $resolved,
            'tenantUrl' => $resolved === 'ready' ? $url : null,
            'companyName' => is_string($pending['company_name'] ?? null) ? $pending['company_name'] : null,
            'email' => is_string($pending['email'] ?? null) ? $pending['email'] : null,
        ];
    }

    protected function tenant(): ?Tenant
    {
        $tenantId = session('registration.pending.tenant_id');

        if (! is_string($tenantId) || ! TenantProvisioner::validTenantId($tenantId)) {
            return null;
        }

        return Tenant::query()->find($tenantId);
    }
}
