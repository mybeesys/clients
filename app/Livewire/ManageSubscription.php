<?php

namespace App\Livewire;

use App\Models\Company;
use App\Services\Entitlements\CouponApplicator;
use App\Services\Entitlements\EntitlementCatalog;
use App\Services\Entitlements\EntitlementPricer;
use App\Services\Entitlements\EntitlementProvisioner;
use App\Services\HyperPay\HyperPayCheckoutService;
use App\Services\HyperPay\HyperPayConfig;
use App\Support\TenantApplicationUrl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.subscribe')]
class ManageSubscription extends Component
{
    /** @var 'choose'|'renew'|'customize' */
    public string $mode = 'choose';

    public array $plan_config = [];

    public ?string $renew_coupon = null;

    public string $renew_period = 'Year';

    public bool $saving = false;

    public function mount(): void
    {
        $company = $this->company();

        if (! $company) {
            abort(403);
        }

        $entitlement = $company->entitlement;
        $catalog = app(EntitlementCatalog::class);
        $modules = $entitlement?->modules ?: [];
        $includedEmployees = $catalog->includedEmployees($modules);

        $this->plan_config = [
            'period' => 'Year',
            'modules' => $modules,
            'employees' => max($includedEmployees, (int) ($entitlement?->employees_quota ?: $includedEmployees)),
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
        $this->renew_period = 'Year';
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

            $entitlement = $company->entitlement;
            if (! $entitlement) {
                throw ValidationException::withMessages([
                    'renew' => __('main.subscribe.renew_requires_custom_package'),
                ]);
            }

            $planConfig = [
                'period' => $this->renew_period === 'Month' ? 'Month' : 'Year',
                'modules' => $entitlement->modules ?? [],
                'employees' => (int) $entitlement->employees_quota,
                'establishments' => (int) $entitlement->establishments_quota,
                'screen_devices' => (int) $entitlement->screen_devices_quota,
                'coupon_code' => filled($this->renew_coupon) ? $this->renew_coupon : null,
            ];

            return $this->startCardCheckout($company, $planConfig, 'renew', 'renew');
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

            return $this->startCardCheckout($company, $config, 'replace', 'plan_config');
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
                    'period' => $this->renew_period === 'Month' ? 'Month' : 'Year',
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
            'yearlyMonthsCharged' => $catalog->yearlyMonthsCharged(),
            'yearlyMonthsGifted' => $catalog->yearlyMonthsGifted(),
        ]);
    }

    protected function company(): ?Company
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->defaultCompany() ?? $user?->company;
    }

    /**
     * @param  array<string, mixed>  $planConfig
     */
    protected function startCardCheckout(Company $company, array $planConfig, string $intent, string $errorBag): mixed
    {
        $quote = app(EntitlementPricer::class)->quote($planConfig);

        if ((float) ($quote['period_total'] ?? 0) <= 0) {
            if ($intent === 'renew') {
                app(EntitlementProvisioner::class)->renew($company, [
                    'coupon_code' => $planConfig['coupon_code'] ?? null,
                    'period' => ($planConfig['period'] ?? 'Year') === 'Month' ? 'Month' : 'Year',
                ]);
            } else {
                app(EntitlementProvisioner::class)->replace($company, $planConfig);
            }

            return $this->redirectToTenant($company);
        }

        if (! HyperPayConfig::instance()->isConfigured()) {
            $this->saving = false;
            $this->addError($errorBag, __('fields.hyperpay_not_configured'));

            return null;
        }

        $token = Str::random(40);

        try {
            $payment = HyperPayCheckoutService::instance()->startSubscription(
                $company,
                $planConfig,
                $quote,
                HyperPayCheckoutService::billingFromCompany($company, Auth::user()),
                $intent,
                $token,
            );
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->saving = false;
            $this->addError($errorBag, $exception->getMessage());

            return null;
        }

        session([
            'hyperpay.checkout_uid' => $payment->uid,
            'hyperpay.checkout_token' => $token,
        ]);

        return $this->redirect(route('hyperpay.checkout', ['uid' => $payment->uid]), navigate: false);
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
