<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantProvisionSteps;
use App\Support\TenantApplicationUrl;
use App\Support\TenantProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class RegistrationProgressController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        $tenant = $this->tenant();

        if ($tenant === null) {
            return redirect()->route('filament.admin.auth.login');
        }

        if ((string) $tenant->provision_error === 'provision_start_failed') {
            $tenant->provision_status = 'queued';
            $tenant->provision_error = null;
            $tenant->save();
        }

        return response()
            ->view('auth.registration-progress', $this->viewData())
            ->header('Cache-Control', 'no-store');
    }

    public function status(): JsonResponse
    {
        $this->advanceTenant();
        $state = $this->viewData();

        if ($state['status'] === 'missing') {
            return response()->json($state, 404);
        }

        return response()->json([
            'status' => $state['status'],
            'tenant_url' => $state['tenantUrl'],
            'detail' => $state['detail'],
            'error' => $state['error'],
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
        }

        return redirect()->route('register.pending');
    }

    /**
     * @return array{status: string, tenantUrl: ?string, companyName: ?string, email: ?string, detail: string, error: ?string}
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
                'detail' => '',
                'error' => null,
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

        $error = is_string($tenant->provision_error ?? null) && $tenant->provision_error !== ''
            ? $tenant->provision_error
            : null;

        return [
            'status' => $resolved,
            'tenantUrl' => $resolved === 'ready' ? $url : null,
            'companyName' => is_string($pending['company_name'] ?? null) ? $pending['company_name'] : null,
            'email' => is_string($pending['email'] ?? null) ? $pending['email'] : null,
            'detail' => $resolved === 'pending' ? app(TenantProvisionSteps::class)->detail($tenant) : '',
            'error' => $resolved === 'failed' ? $error : null,
        ];
    }

    protected function advanceTenant(): void
    {
        $tenant = $this->tenant();

        if ($tenant === null) {
            return;
        }

        $status = (string) ($tenant->provision_status ?: 'queued');

        if ($status === 'ready' || $status === 'failed') {
            return;
        }

        app(TenantProvisionSteps::class)->advance($tenant, 50, 1);
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
