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
use Livewire\Component;
use RuntimeException;

class ManageSubscription extends Component
{
    /** @var 'choose'|'renew'|'customize'|'addon' */
    public string $mode = 'choose';

    public array $plan_config = [];

    /** @var array<string, int> */
    public array $addon_quotas = [];

    /** @var array<string, int> */
    public array $addon_floors = [];

    public ?string $renew_coupon = null;

    public string $renew_period = 'Year';

    public bool $saving = false;

    public function mount(): void
    {
        if (request()->boolean('embed')) {
            session(['subscribe.embed' => true]);
        }

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
            'pos_devices' => (int) ($entitlement?->pos_devices_quota ?: ($catalog->quota('pos_devices')['included'] ?? 1)),
            'waiter_seats' => (int) ($entitlement?->waiter_seats_quota ?: ($catalog->quota('waiter_seats')['included'] ?? 1)),
            'kitchen_devices' => (int) ($entitlement?->kitchen_devices_quota ?: ($catalog->quota('kitchen_devices')['included'] ?? 1)),
            'coupon_code' => null,
        ];

        $this->seedAddonFromEntitlement($company);

        $requestedMode = request()->query('mode');
        if ($requestedMode === 'addon' && $entitlement) {
            $this->mode = 'addon';
        }
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

    public function showAddon(): void
    {
        $company = $this->company();
        if (! $company?->entitlement) {
            $this->addError('addon', __('main.subscribe.addon_requires_package'));

            return;
        }

        $this->seedAddonFromEntitlement($company);
        $this->mode = 'addon';
        $this->resetErrorBag();
    }

    public function showChoose(): void
    {
        $this->mode = 'choose';
        $this->resetErrorBag();
    }

    public function bumpAddon(string $key, int $delta): void
    {
        if (! in_array($key, EntitlementPricer::ADDON_QUOTA_KEYS, true)) {
            return;
        }

        $catalog = app(EntitlementCatalog::class);
        $quota = $catalog->quota($key);
        $max = (int) ($quota['max'] ?? 9999);
        $floor = (int) ($this->addon_floors[$key] ?? 0);
        $current = (int) ($this->addon_quotas[$key] ?? $floor);
        $this->addon_quotas[$key] = max($floor, min($max, $current + $delta));
        $this->resetErrorBag('addon');
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
                'intent' => 'renew',
                'period' => $this->renew_period === 'Month' ? 'Month' : 'Year',
                'modules' => $entitlement->modules ?? [],
                'employees' => (int) $entitlement->employees_quota,
                'establishments' => (int) $entitlement->establishments_quota,
                'screen_devices' => (int) $entitlement->screen_devices_quota,
                'pos_devices' => (int) ($entitlement->pos_devices_quota ?? 0),
                'waiter_seats' => (int) ($entitlement->waiter_seats_quota ?? 0),
                'kitchen_devices' => (int) ($entitlement->kitchen_devices_quota ?? 0),
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
            $config['intent'] = 'replace';

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

    public function buyAddon(): mixed
    {
        $this->saving = true;

        try {
            $company = $this->company();
            abort_unless($company, 403);

            $quote = app(EntitlementPricer::class)->quoteAddon($company, $this->addon_quotas);

            $planConfig = [
                'intent' => 'addon',
                'period' => $quote['period'],
                'modules' => $quote['modules'],
                'employees' => $quote['employees'],
                'establishments' => $quote['establishments'],
                'screen_devices' => $quote['screen_devices'],
                'pos_devices' => $quote['pos_devices'],
                'waiter_seats' => $quote['waiter_seats'],
                'kitchen_devices' => $quote['kitchen_devices'],
                'remaining_months' => $quote['remaining_months'],
                'coupon_code' => null,
            ];

            return $this->startCardCheckout($company, $planConfig, 'addon', 'addon', $quote);
        } catch (ValidationException $e) {
            $this->saving = false;
            throw $e;
        } catch (\Throwable $e) {
            $this->saving = false;
            $this->addError('addon', $e->getMessage());

            return null;
        }
    }

    public function render()
    {
        $company = $this->company();
        $entitlement = $company?->entitlement;
        $subscription = $company?->subscription;
        $catalog = app(EntitlementCatalog::class);
        $pricer = app(EntitlementPricer::class);
        $localeIsAr = app()->getLocale() === 'ar';

        $renewQuote = null;
        if ($entitlement) {
            try {
                $renewQuote = $pricer->quote([
                    'period' => $this->renew_period === 'Month' ? 'Month' : 'Year',
                    'modules' => $entitlement->modules ?? [],
                    'employees' => (int) $entitlement->employees_quota,
                    'establishments' => (int) $entitlement->establishments_quota,
                    'screen_devices' => (int) $entitlement->screen_devices_quota,
                    'pos_devices' => (int) ($entitlement->pos_devices_quota ?? 0),
                    'waiter_seats' => (int) ($entitlement->waiter_seats_quota ?? 0),
                    'kitchen_devices' => (int) ($entitlement->kitchen_devices_quota ?? 0),
                    'coupon_code' => filled($this->renew_coupon) ? $this->renew_coupon : null,
                ]);
            } catch (\Throwable) {
                $renewQuote = null;
            }
        }

        $addonQuote = null;
        $addonError = null;
        if ($this->mode === 'addon' && $entitlement) {
            try {
                $addonQuote = $pricer->quoteAddon($company, $this->addon_quotas);
            } catch (ValidationException $e) {
                $addonError = collect($e->errors())->flatten()->first();
            } catch (\Throwable $e) {
                $addonError = $e->getMessage();
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

        $modules = array_values(array_unique(array_merge(['platform'], $entitlement?->modules ?? [])));
        $addonRows = [];
        foreach (EntitlementPricer::ADDON_QUOTA_KEYS as $key) {
            $quota = $catalog->quota($key);
            if (! $quota) {
                continue;
            }
            $linked = $quota['linked_module'] ?? null;
            $requiresModule = in_array($key, ['screen_devices', 'pos_devices', 'waiter_seats', 'kitchen_devices'], true);
            if ($requiresModule && $linked && ! in_array($linked, $modules, true)) {
                continue;
            }
            $addonRows[] = [
                'key' => $key,
                'name' => $localeIsAr
                    ? ($quota['name_ar'] ?? $quota['name_en'] ?? $key)
                    : ($quota['name_en'] ?? $quota['name_ar'] ?? $key),
                'unit' => (float) ($quota['price_per_extra_month'] ?? 0),
                'max' => (int) ($quota['max'] ?? 9999),
                'floor' => (int) ($this->addon_floors[$key] ?? 0),
                'value' => (int) ($this->addon_quotas[$key] ?? 0),
            ];
        }

        return view('livewire.manage-subscription', [
            'company' => $company,
            'entitlement' => $entitlement,
            'subscription' => $subscription,
            'catalog' => $catalog->toFrontend(),
            'renewQuote' => $renewQuote,
            'addonQuote' => $addonQuote,
            'addonError' => $addonError,
            'addonRows' => $addonRows,
            'moduleLabels' => $moduleLabels,
            'tenantUrl' => $company ? TenantApplicationUrl::forCompany($company) : null,
            'hasCustomPackage' => (bool) $entitlement,
            'yearlyMonthsCharged' => $catalog->yearlyMonthsCharged(),
            'yearlyMonthsGifted' => $catalog->yearlyMonthsGifted(),
            'hasActiveCoupon' => app(CouponApplicator::class)->hasActive(),
        ])->layout($this->isEmbed() ? 'layouts.subscribe-embed' : 'layouts.subscribe');
    }

    protected function isEmbed(): bool
    {
        return (bool) (request()->boolean('embed') || session('subscribe.embed'));
    }

    protected function company(): ?Company
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $handoffCompanyId = session('subscribe.company_id');
        if ($handoffCompanyId) {
            $company = Company::query()
                ->where('id', (int) $handoffCompanyId)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhereHas('members', fn ($members) => $members->where('users.id', $user->id));
                })
                ->first();

            if ($company) {
                return $company;
            }
        }

        return $user->defaultCompany() ?? $user->company;
    }

    protected function breakOutTo(string $url): mixed
    {
        $safe = json_encode($url, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        return $this->js('window.top.location.href = '.$safe);
    }

    protected function seedAddonFromEntitlement(Company $company): void
    {
        $entitlement = $company->entitlement;
        if (! $entitlement) {
            $this->addon_quotas = [];
            $this->addon_floors = [];

            return;
        }

        $current = app(EntitlementPricer::class)->syncEffectiveQuotas($entitlement);
        $this->addon_floors = $current;
        $this->addon_quotas = $current;
    }

    /**
     * @param  array<string, mixed>  $planConfig
     * @param  array<string, mixed>|null  $precomputedQuote
     */
    protected function startCardCheckout(
        Company $company,
        array $planConfig,
        string $intent,
        string $errorBag,
        ?array $precomputedQuote = null,
    ): mixed {
        $quote = $precomputedQuote ?? (
            $intent === 'addon'
                ? app(EntitlementPricer::class)->quoteAddon($company, $planConfig)
                : app(EntitlementPricer::class)->quote($planConfig)
        );

        if ((float) ($quote['period_total'] ?? 0) <= 0) {
            if ($intent === 'renew') {
                app(EntitlementProvisioner::class)->renew($company, [
                    'coupon_code' => $planConfig['coupon_code'] ?? null,
                    'period' => ($planConfig['period'] ?? 'Year') === 'Month' ? 'Month' : 'Year',
                ]);
            } elseif ($intent === 'addon') {
                app(EntitlementProvisioner::class)->applyAddon($company, $planConfig);
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

        $checkoutUrl = route('hyperpay.checkout', ['uid' => $payment->uid]);

        if ($this->isEmbed()) {
            return $this->breakOutTo($checkoutUrl);
        }

        return $this->redirect($checkoutUrl, navigate: false);
    }

    protected function redirectToTenant(Company $company): mixed
    {
        $url = TenantApplicationUrl::forCompany($company);

        if ($url) {
            $target = $url.'/subscription';

            if ($this->isEmbed()) {
                return $this->breakOutTo($target);
            }

            return redirect()->away($target);
        }

        return redirect()->route('subscribe')->with('status', __('main.subscribe.updated'));
    }
}
