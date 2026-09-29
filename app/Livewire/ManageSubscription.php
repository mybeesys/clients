<?php

namespace App\Livewire;

use App\Models\Company;
use App\Services\Entitlements\CouponApplicator;
use App\Services\Entitlements\EntitlementCatalog;
use App\Services\Entitlements\EntitlementPricer;
use App\Services\Entitlements\EntitlementProvisioner;
use App\Support\TenantApplicationUrl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.subscribe')]
class ManageSubscription extends Component
{
    /** @var 'choose'|'renew'|'customize' */
    public string $mode = 'choose';

    public array $plan_config = [];

    public ?string $renew_coupon = null;

    public bool $saving = false;

    public function mount(): void
    {
        $company = $this->company();

        if (! $company) {
            abort(403);
        }

        $entitlement = $company->entitlement;
        $catalog = app(EntitlementCatalog::class);

        $this->plan_config = [
            'period' => $entitlement?->period ?: 'Month',
            'modules' => $entitlement?->modules ?: [],
            'employees' => (int) ($entitlement?->employees_quota ?: ($catalog->quota('employees')['included'] ?? 5)),
            'establishments' => (int) ($entitlement?->establishments_quota ?: ($catalog->quota('establishments')['included'] ?? 1)),
            'screen_devices' => (int) ($entitlement?->screen_devices_quota ?: ($catalog->quota('screen_devices')['included'] ?? 1)),
            'coupon_code' => null,
        ];
    }

    public function showRenew(): void
    {
        if (! $this->company()?->entitlement) {
            $this->addError('renew', __('main.subscribe.renew_requires_custom_package'));

            return;
        }

        $this->mode = 'renew';
        $this->resetErrorBag();
    }

    public function showCustomize(): void
    {
        $this->mode = 'customize';
        $this->resetErrorBag();
    }

    public function showChoose(): void
    {
        $this->mode = 'choose';
        $this->resetErrorBag();
    }

    /**
     * @return array{ok: bool, message?: string, code?: string, type?: string, value?: float, discount?: float, label?: string}
     */
    public function previewPlanCoupon(?string $code = null, float $amount = 0): array
    {
        return app(CouponApplicator::class)->preview($code, max(0, $amount));
    }

    public function renewCurrent(): mixed
    {
        $this->saving = true;

        try {
            $company = $this->company();
            abort_unless($company, 403);

            app(EntitlementProvisioner::class)->renew($company, [
                'coupon_code' => filled($this->renew_coupon) ? $this->renew_coupon : null,
            ]);

            return $this->redirectToTenant($company);
        } catch (ValidationException $e) {
            $this->saving = false;
            throw $e;
        } catch (\Throwable $e) {
            $this->saving = false;
            $this->addError('renew', $e->getMessage());

            return null;
        }
    }

    public function saveNewPackage(): mixed
    {
        $this->saving = true;

        try {
            $company = $this->company();
            abort_unless($company, 403);

            $config = $this->plan_config;

            if (! is_array($config) || empty($config['modules'])) {
                throw ValidationException::withMessages([
                    'plan_config' => __('main.wizard.plan_modules_required'),
                ]);
            }

            app(EntitlementProvisioner::class)->replace($company, $config);

            return $this->redirectToTenant($company);
        } catch (ValidationException $e) {
            $this->saving = false;
            throw $e;
        } catch (\Throwable $e) {
            $this->saving = false;
            $this->addError('plan_config', $e->getMessage());

            return null;
        }
    }

    public function render()
    {
        $company = $this->company();
        $entitlement = $company?->entitlement;
        $subscription = $company?->subscription;
        $catalog = app(EntitlementCatalog::class);
        $localeIsAr = app()->getLocale() === 'ar';

        $renewQuote = null;
        if ($entitlement) {
            try {
                $renewQuote = app(EntitlementPricer::class)->quote([
                    'period' => $entitlement->period,
                    'modules' => $entitlement->modules ?? [],
                    'employees' => (int) $entitlement->employees_quota,
                    'establishments' => (int) $entitlement->establishments_quota,
                    'screen_devices' => (int) $entitlement->screen_devices_quota,
                    'coupon_code' => filled($this->renew_coupon) ? $this->renew_coupon : null,
                ]);
            } catch (\Throwable) {
                $renewQuote = null;
            }
        }

        $moduleLabels = collect($entitlement?->modules ?? [])
            ->map(function (string $key) use ($catalog, $localeIsAr) {
                try {
                    $module = $catalog->module($key);

                    return $localeIsAr
                        ? ($module['name_ar'] ?? $module['name_en'] ?? $key)
                        : ($module['name_en'] ?? $module['name_ar'] ?? $key);
                } catch (\Throwable) {
                    return $key;
                }
            })
            ->values()
            ->all();

        return view('livewire.manage-subscription', [
            'company' => $company,
            'entitlement' => $entitlement,
            'subscription' => $subscription,
            'catalog' => $catalog->toFrontend(),
            'renewQuote' => $renewQuote,
            'moduleLabels' => $moduleLabels,
            'tenantUrl' => $company ? TenantApplicationUrl::forCompany($company) : null,
            'hasCustomPackage' => (bool) $entitlement,
        ]);
    }

    protected function company(): ?Company
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->defaultCompany() ?? $user?->company;
    }

    protected function redirectToTenant(Company $company): mixed
    {
        $url = TenantApplicationUrl::forCompany($company);

        if ($url) {
            return redirect()->away($url.'/subscription');
        }

        return redirect()->route('subscribe')->with('status', __('main.subscribe.updated'));
    }
}
