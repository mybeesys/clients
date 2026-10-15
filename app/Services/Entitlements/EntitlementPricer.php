<?php

namespace App\Services\Entitlements;

use App\Models\Company;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EntitlementPricer
{
    /** @var list<string> */
    public const ADDON_QUOTA_KEYS = [
        'employees',
        'establishments',
        'screen_devices',
        'pos_devices',
        'waiter_seats',
        'kitchen_devices',
    ];

    public function __construct(
        protected EntitlementCatalog $catalog,
        protected CouponApplicator $coupons,
    ) {}

    /**
     * @param  array{
     *     period?: string,
     *     modules?: list<string>,
     *     employees?: int,
     *     establishments?: int,
     *     screen_devices?: int,
     *     coupon_code?: string|null
     * }  $input
     * @return array{
     *     period: string,
     *     modules: list<string>,
     *     employees: int,
     *     included_employees: int,
     *     establishments: int,
     *     screen_devices: int,
     *     currency: string,
     *     monthly_subtotal: float,
     *     period_subtotal: float,
     *     discount: float,
     *     period_total: float,
     *     yearly_months_charged: int,
     *     coupon_code: string|null,
     *     coupon_id: int|null,
     *     line_items: list<array{key: string, label: string, quantity: int, unit_price: float, total: float, type: string}>
     * }
     */
    public function quote(array $input): array
    {
        $period = ($input['period'] ?? 'Month') === 'Year' ? 'Year' : 'Month';
        $modules = $this->catalog->resolveDependencies($input['modules'] ?? []);

        if ($modules === []) {
            throw ValidationException::withMessages([
                'plan_config.modules' => __('main.wizard.plan_modules_required'),
            ]);
        }

        $missingSoft = $this->catalog->missingRequiresAny($modules);
        if ($missingSoft !== []) {
            throw ValidationException::withMessages([
                'plan_config.modules' => __('main.wizard.plan_reports_needs_data_module'),
            ]);
        }

        $includedEmployees = $this->catalog->includedEmployees($modules);
        $employees = $this->clampEmployees((int) ($input['employees'] ?? 0), $includedEmployees);
        $establishments = $this->clampQuota('establishments', (int) ($input['establishments'] ?? 0));

        $screenDevices = 0;
        if (in_array('digital_screens', $modules, true)) {
            $screenDevices = $this->clampQuota('screen_devices', (int) ($input['screen_devices'] ?? 0));
        }

        $posDevices = 0;
        if (in_array('cashier_pos', $modules, true)) {
            $posDevices = $this->clampQuota('pos_devices', (int) ($input['pos_devices'] ?? 0));
        }

        $waiterSeats = 0;
        if (in_array('waiter_app', $modules, true)) {
            $waiterSeats = $this->clampQuota('waiter_seats', (int) ($input['waiter_seats'] ?? 0));
        }

        $kitchenDevices = 0;
        if (in_array('kitchen_app', $modules, true)) {
            $kitchenDevices = $this->clampQuota('kitchen_devices', (int) ($input['kitchen_devices'] ?? 0));
        }

        $lineItems = [];
        $monthly = 0.0;
        $localeIsAr = app()->getLocale() === 'ar';

        // Platform foundation (products, employees, branches, settings) is never billed —
        // it is granted free with any purchased module.

        foreach ($modules as $key) {
            $module = $this->catalog->module($key);
            $price = (float) ($module['price_month'] ?? 0);
            $lineItems[] = [
                'key' => $key,
                'label' => $localeIsAr ? ($module['name_ar'] ?? $module['name_en']) : ($module['name_en'] ?? $module['name_ar']),
                'quantity' => 1,
                'unit_price' => $price,
                'total' => $price,
                'type' => 'module',
            ];
            $monthly += $price;
        }

        $monthly += $this->appendExtraQuota(
            $lineItems,
            'employees',
            $employees,
            fn (int $extra) => __('main.wizard.plan_extra_employees', ['count' => $extra]),
            $includedEmployees,
        );
        $monthly += $this->appendExtraQuota(
            $lineItems,
            'establishments',
            $establishments,
            fn (int $extra) => __('main.wizard.plan_extra_branches', ['count' => $extra])
        );

        if ($screenDevices > 0) {
            $monthly += $this->appendExtraQuota(
                $lineItems,
                'screen_devices',
                $screenDevices,
                fn (int $extra) => __('main.wizard.plan_extra_screen_devices', ['count' => $extra])
            );
        }

        if ($posDevices > 0) {
            $monthly += $this->appendExtraQuota(
                $lineItems,
                'pos_devices',
                $posDevices,
                fn (int $extra) => __('main.wizard.plan_extra_pos_devices', ['count' => $extra])
            );
        }

        if ($waiterSeats > 0) {
            $monthly += $this->appendExtraQuota(
                $lineItems,
                'waiter_seats',
                $waiterSeats,
                fn (int $extra) => __('main.wizard.plan_extra_waiter_seats', ['count' => $extra])
            );
        }

        if ($kitchenDevices > 0) {
            $monthly += $this->appendExtraQuota(
                $lineItems,
                'kitchen_devices',
                $kitchenDevices,
                fn (int $extra) => __('main.wizard.plan_extra_kitchen_devices', ['count' => $extra])
            );
        }

        $yearlyMonths = $this->catalog->yearlyMonthsCharged();
        $periodSubtotal = $period === 'Year'
            ? round($monthly * $yearlyMonths, 2)
            : round($monthly, 2);

        $couponCode = isset($input['coupon_code']) ? strtoupper(trim((string) $input['coupon_code'])) : null;
        $couponId = null;
        $discount = 0.0;

        if (filled($couponCode)) {
            $coupon = $this->coupons->requireValid($couponCode);
            $discount = $this->coupons->discountAmount($coupon, $periodSubtotal);
            $couponId = $coupon->id;
            $couponCode = $coupon->code;

            $lineItems[] = [
                'key' => 'coupon_discount',
                'label' => $this->coupons->label($coupon),
                'quantity' => 1,
                'unit_price' => -$discount,
                'total' => -$discount,
                'type' => 'discount',
            ];
        } else {
            $couponCode = null;
        }

        $periodTotal = round(max(0, $periodSubtotal - $discount), 2);

        return [
            'period' => $period,
            'modules' => $modules,
            'employees' => $employees,
            'included_employees' => $includedEmployees,
            'establishments' => $establishments,
            'screen_devices' => $screenDevices,
            'pos_devices' => $posDevices,
            'waiter_seats' => $waiterSeats,
            'kitchen_devices' => $kitchenDevices,
            'currency' => $this->catalog->currency(),
            'monthly_subtotal' => round($monthly, 2),
            'period_subtotal' => $periodSubtotal,
            'discount' => $discount,
            'period_total' => $periodTotal,
            'yearly_months_charged' => $yearlyMonths,
            'coupon_code' => $couponCode,
            'coupon_id' => $couponId,
            'line_items' => $lineItems,
        ];
    }

    /**
     * Mid-cycle quota top-up: bill only deltas × remaining months (no modules, no yearly gift).
     *
     * @param  array{
     *     employees?: int,
     *     establishments?: int,
     *     screen_devices?: int,
     *     pos_devices?: int,
     *     waiter_seats?: int,
     *     kitchen_devices?: int
     * }  $targetQuotas
     * @return array{
     *     intent: string,
     *     modules: list<string>,
     *     employees: int,
     *     establishments: int,
     *     screen_devices: int,
     *     pos_devices: int,
     *     waiter_seats: int,
     *     kitchen_devices: int,
     *     current: array<string, int>,
     *     deltas: array<string, int>,
     *     remaining_months: int,
     *     currency: string,
     *     monthly_subtotal: float,
     *     period_subtotal: float,
     *     discount: float,
     *     period_total: float,
     *     coupon_code: null,
     *     coupon_id: null,
     *     period: string,
     *     line_items: list<array{key: string, label: string, quantity: int, unit_price: float, total: float, type: string, meta?: array}>
     * }
     */
    public function quoteAddon(Company $company, array $targetQuotas): array
    {
        $entitlement = $company->entitlement;
        if (! $entitlement) {
            throw ValidationException::withMessages([
                'addon' => __('main.subscribe.addon_requires_package'),
            ]);
        }

        $subscription = $company->subscription;
        $expiredAt = $subscription?->expired_at ? Carbon::parse($subscription->expired_at) : null;
        if (! $expiredAt || $expiredAt->lte(now())) {
            throw ValidationException::withMessages([
                'addon' => __('main.subscribe.addon_requires_active'),
            ]);
        }

        $remainingMonths = $this->remainingMonthsUntil($expiredAt);
        $modules = array_values(array_unique(array_merge(['platform'], $entitlement->modules ?? [])));
        // Lift stale 0 quotas to the included floor when the module is entitled,
        // so mid-cycle add-ons never charge for capacity the tenant already has.
        $current = $this->syncEffectiveQuotas($entitlement);
        $localeIsAr = app()->getLocale() === 'ar';

        $resolved = [];
        $deltas = [];
        $lineItems = [];
        $monthly = 0.0;

        foreach (self::ADDON_QUOTA_KEYS as $key) {
            $currentValue = $current[$key];
            $requested = array_key_exists($key, $targetQuotas)
                ? (int) $targetQuotas[$key]
                : $currentValue;

            if ($requested < $currentValue) {
                throw ValidationException::withMessages([
                    "addon.{$key}" => __('main.subscribe.addon_cannot_decrease'),
                ]);
            }

            // Device-style quotas require their module; seats/branches stay open on any package.
            $linkedModule = $this->catalog->quota($key)['linked_module'] ?? null;
            $requiresModule = in_array($key, ['screen_devices', 'pos_devices', 'waiter_seats', 'kitchen_devices'], true);
            if ($requiresModule && $linkedModule && $requested > $currentValue && ! in_array($linkedModule, $modules, true)) {
                throw ValidationException::withMessages([
                    "addon.{$key}" => __('main.subscribe.addon_module_required', [
                        'quota' => $localeIsAr
                            ? ($this->catalog->quota($key)['name_ar'] ?? $key)
                            : ($this->catalog->quota($key)['name_en'] ?? $key),
                    ]),
                ]);
            }

            $target = $this->clampAddonQuota($key, $requested, $currentValue);
            $resolved[$key] = $target;
            $delta = max(0, $target - $currentValue);
            $deltas[$key] = $delta;

            if ($delta <= 0) {
                continue;
            }

            $quota = $this->catalog->quota($key);
            $unit = (float) ($quota['price_per_extra_month'] ?? 0);
            $lineMonthly = $delta * $unit;
            $lineTotal = round($lineMonthly * $remainingMonths, 2);
            $monthly += $lineMonthly;

            $label = $this->addonLineLabel($key, $delta, $remainingMonths);

            $lineItems[] = [
                'key' => "{$key}_addon",
                'label' => $label,
                'quantity' => $delta,
                'unit_price' => $unit,
                'total' => $lineTotal,
                'type' => 'quota_addon',
                'meta' => [
                    'quota_key' => $key,
                    'months' => $remainingMonths,
                    'monthly_unit' => $unit,
                ],
            ];
        }

        if ($lineItems === []) {
            throw ValidationException::withMessages([
                'addon' => __('main.subscribe.addon_no_changes'),
            ]);
        }

        $periodSubtotal = round($monthly * $remainingMonths, 2);

        return [
            'intent' => 'addon',
            'modules' => array_values(array_filter($modules, fn ($m) => $m !== 'platform')),
            'employees' => $resolved['employees'],
            'establishments' => $resolved['establishments'],
            'screen_devices' => $resolved['screen_devices'],
            'pos_devices' => $resolved['pos_devices'],
            'waiter_seats' => $resolved['waiter_seats'],
            'kitchen_devices' => $resolved['kitchen_devices'],
            'current' => $current,
            'deltas' => $deltas,
            'remaining_months' => $remainingMonths,
            'currency' => $this->catalog->currency(),
            'monthly_subtotal' => round($monthly, 2),
            'period_subtotal' => $periodSubtotal,
            'discount' => 0.0,
            'period_total' => $periodSubtotal,
            'coupon_code' => null,
            'coupon_id' => null,
            'period' => ($entitlement->period === 'Year') ? 'Year' : 'Month',
            'line_items' => $lineItems,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function currentQuotasFromEntitlement($entitlement): array
    {
        $modules = array_values(array_unique(array_merge(
            ['platform'],
            is_array($entitlement->modules ?? null) ? $entitlement->modules : []
        )));

        $raw = [
            'employees' => (int) ($entitlement->employees_quota ?? 0),
            'establishments' => (int) ($entitlement->establishments_quota ?? 0),
            'screen_devices' => (int) ($entitlement->screen_devices_quota ?? 0),
            'pos_devices' => (int) ($entitlement->pos_devices_quota ?? 0),
            'waiter_seats' => (int) ($entitlement->waiter_seats_quota ?? 0),
            'kitchen_devices' => (int) ($entitlement->kitchen_devices_quota ?? 0),
        ];

        foreach ($raw as $key => $stored) {
            $raw[$key] = $this->effectiveQuotaFloor($key, $stored, $modules);
        }

        return $raw;
    }

    /**
     * Persist effective floors when stored quotas lag behind entitled included amounts.
     *
     * @return array<string, int>
     */
    public function syncEffectiveQuotas($entitlement): array
    {
        $effective = $this->currentQuotasFromEntitlement($entitlement);
        $columns = [
            'employees' => 'employees_quota',
            'establishments' => 'establishments_quota',
            'screen_devices' => 'screen_devices_quota',
            'pos_devices' => 'pos_devices_quota',
            'waiter_seats' => 'waiter_seats_quota',
            'kitchen_devices' => 'kitchen_devices_quota',
        ];

        $payload = [];
        foreach ($columns as $key => $column) {
            $stored = (int) ($entitlement->{$column} ?? 0);
            $floor = (int) ($effective[$key] ?? 0);
            if ($floor > $stored) {
                $payload[$column] = $floor;
            }
        }

        if ($payload !== []) {
            $entitlement->forceFill($payload)->save();
            $entitlement->refresh();
        }

        return $this->currentQuotasFromEntitlement($entitlement);
    }

    /**
     * @param  list<string>  $modules
     */
    protected function effectiveQuotaFloor(string $key, int $stored, array $modules): int
    {
        $def = $this->catalog->quota($key) ?? [];
        $included = (int) ($def['included'] ?? 0);
        $linked = $def['linked_module'] ?? null;

        if (in_array($key, ['screen_devices', 'pos_devices', 'waiter_seats', 'kitchen_devices'], true)) {
            if (! $linked || ! in_array($linked, $modules, true)) {
                return 0;
            }

            return max($stored, max(1, $included));
        }

        if ($key === 'employees') {
            $commercial = array_values(array_filter($modules, fn ($m) => $m !== 'platform'));
            $bundle = $this->catalog->includedEmployees($commercial);

            return max($stored, max($included, $bundle, 1));
        }

        // establishments (and any unlinked seat-style quota)
        return max($stored, max(1, $included));
    }

    public function remainingMonthsUntil(Carbon $expiredAt): int
    {
        $seconds = max(1, $expiredAt->getTimestamp() - now()->getTimestamp());
        $days = max(1, (int) ceil($seconds / 86400));

        return max(1, (int) ceil($days / 30));
    }

    protected function clampAddonQuota(string $key, int $value, int $floor): int
    {
        $quota = $this->catalog->quota($key);
        $max = (int) ($quota['max'] ?? 9999);

        return max($floor, min($max, $value));
    }

    protected function addonLineLabel(string $key, int $delta, int $months): string
    {
        $base = match ($key) {
            'employees' => __('main.wizard.plan_extra_employees', ['count' => $delta]),
            'establishments' => __('main.wizard.plan_extra_branches', ['count' => $delta]),
            'screen_devices' => __('main.wizard.plan_extra_screen_devices', ['count' => $delta]),
            'pos_devices' => __('main.wizard.plan_extra_pos_devices', ['count' => $delta]),
            'waiter_seats' => __('main.wizard.plan_extra_waiter_seats', ['count' => $delta]),
            'kitchen_devices' => __('main.wizard.plan_extra_kitchen_devices', ['count' => $delta]),
            default => $key.' (+'.$delta.')',
        };

        return $base.' · '.__('main.subscribe.addon_months_suffix', ['months' => $months]);
    }

    protected function clampQuota(string $key, int $value): int
    {
        $quota = $this->catalog->quota($key);

        if (! $quota) {
            return max(0, $value);
        }

        $min = (int) ($quota['min'] ?? 0);
        $max = (int) ($quota['max'] ?? 9999);
        $default = (int) ($quota['included'] ?? $min);

        if ($value <= 0) {
            $value = $default;
        }

        return max($min, min($max, $value));
    }

    protected function clampEmployees(int $value, int $included): int
    {
        $quota = $this->catalog->quota('employees');
        $max = (int) ($quota['max'] ?? 9999);
        $floor = max((int) ($quota['min'] ?? 0), $included);

        if ($value <= 0) {
            $value = $floor;
        }

        return max($floor, min($max, $value));
    }

    /**
     * @param  list<array{key: string, label: string, quantity: int, unit_price: float, total: float, type: string}>  $lineItems
     * @param  callable(int): string  $label
     */
    protected function appendExtraQuota(array &$lineItems, string $key, int $count, callable $label, ?int $included = null): float
    {
        $quota = $this->catalog->quota($key);

        if (! $quota) {
            return 0;
        }

        $extra = max(0, $count - (int) ($included ?? $quota['included'] ?? 0));

        if ($extra <= 0) {
            return 0;
        }

        $unit = (float) ($quota['price_per_extra_month'] ?? 0);
        $total = $extra * $unit;

        $lineItems[] = [
            'key' => "{$key}_extra",
            'label' => $label($extra),
            'quantity' => $extra,
            'unit_price' => $unit,
            'total' => $total,
            'type' => 'quota',
        ];

        return $total;
    }
}
