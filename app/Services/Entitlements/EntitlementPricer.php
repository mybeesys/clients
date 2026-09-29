<?php

namespace App\Services\Entitlements;

use Illuminate\Validation\ValidationException;

class EntitlementPricer
{
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

        $employees = $this->clampQuota('employees', (int) ($input['employees'] ?? 0));
        $establishments = $this->clampQuota('establishments', (int) ($input['establishments'] ?? 0));

        $screenDevices = 0;
        if (in_array('digital_screens', $modules, true)) {
            $screenDevices = $this->clampQuota('screen_devices', (int) ($input['screen_devices'] ?? 0));
        }

        $lineItems = [];
        $monthly = 0.0;
        $localeIsAr = app()->getLocale() === 'ar';

        $platform = $this->catalog->platform();
        $platformPrice = (float) ($platform['price_month'] ?? 0);
        $lineItems[] = [
            'key' => $platform['key'] ?? 'platform',
            'label' => $localeIsAr ? ($platform['name_ar'] ?? $platform['name_en']) : ($platform['name_en'] ?? $platform['name_ar']),
            'quantity' => 1,
            'unit_price' => $platformPrice,
            'total' => $platformPrice,
            'type' => 'platform',
        ];
        $monthly += $platformPrice;

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
            fn (int $extra) => __('main.wizard.plan_extra_employees', ['count' => $extra])
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
            'establishments' => $establishments,
            'screen_devices' => $screenDevices,
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

    /**
     * @param  list<array{key: string, label: string, quantity: int, unit_price: float, total: float, type: string}>  $lineItems
     * @param  callable(int): string  $label
     */
    protected function appendExtraQuota(array &$lineItems, string $key, int $count, callable $label): float
    {
        $quota = $this->catalog->quota($key);

        if (! $quota) {
            return 0;
        }

        $extra = max(0, $count - (int) ($quota['included'] ?? 0));

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
