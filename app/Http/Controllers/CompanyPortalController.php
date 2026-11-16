<?php

namespace App\Http\Controllers;

use App\Support\CompanyPortalHandoff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyPortalController extends Controller
{
    public const SESSION_KEY = 'company_portal.pending';

    public function choose(Request $request): View|RedirectResponse
    {
        $pending = $this->pending($request);

        if ($pending === null) {
            return redirect()->route('login');
        }

        if (count($pending['companies']) === 1) {
            return $this->enter($request, (string) $pending['companies'][0]['tenant_id']);
        }

        return view('auth.choose-company', [
            'companies' => $pending['companies'],
            'email' => $pending['email'],
        ]);
    }

    public function enter(Request $request, string $tenantId): RedirectResponse
    {
        $pending = $this->pending($request);

        if ($pending === null) {
            return redirect()->route('login');
        }

        $company = collect($pending['companies'])
            ->first(fn (array $row): bool => (string) $row['tenant_id'] === $tenantId);

        if ($company === null) {
            return redirect()->route('login')
                ->withErrors(['email' => __('main.company_portal.company_unavailable')]);
        }

        if (! empty($pending['user_id']) && empty($company['has_subscription'])) {
            return redirect()->route('subscribe');
        }

        $request->session()->forget(self::SESSION_KEY);

        return redirect()->away(CompanyPortalHandoff::url(
            $pending['email'],
            (string) $company['tenant_id'],
            (string) $company['domain'],
        ));
    }

    /**
     * @return array{
     *     email: string,
     *     user_id: int|null,
     *     companies: list<array{
     *         company_id: int|null,
     *         company_name: string,
     *         tenant_id: string,
     *         domain: string,
     *         has_subscription: bool
     *     }>
     * }|null
     */
    protected function pending(Request $request): ?array
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! is_array($pending) || empty($pending['email']) || empty($pending['companies']) || ! is_array($pending['companies'])) {
            return null;
        }

        return $pending;
    }
}
