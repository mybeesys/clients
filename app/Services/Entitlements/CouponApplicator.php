<?php

namespace App\Services\Entitlements;

use App\Models\Coupon;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CouponApplicator
{
    public function findValid(?string $code, array $context = []): ?Coupon
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return null;
        }

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$code])
            ->where('active', true)
            ->first();

        if (! $coupon) {
            return null;
        }

        $today = Carbon::today();

        if ($coupon->start && $today->lt(Carbon::parse($coupon->start)->startOfDay())) {
            return null;
        }

        if ($coupon->end && $today->gt(Carbon::parse($coupon->end)->endOfDay())) {
            return null;
        }

        if ((int) $coupon->uses_limit > 0 && (int) $coupon->uses_count >= (int) $coupon->uses_limit) {
            return null;
        }

        $period = ($context['period'] ?? null) === 'Year' ? 'Year' : (($context['period'] ?? null) === 'Month' ? 'Month' : null);
        $applicable = $coupon->applicable_period;

        if (filled($applicable) && $period !== null && $applicable !== $period) {
            return null;
        }

        return $coupon;
    }

    /**
     * True when at least one coupon can be entered/applied right now.
     */
    public function hasActive(): bool
    {
        $today = Carbon::today()->toDateString();

        return Coupon::query()
            ->where('active', true)
            ->whereDate('start', '<=', $today)
            ->whereDate('end', '>=', $today)
            ->where(function ($query) {
                $query->where('uses_limit', '<=', 0)
                    ->orWhereColumn('uses_count', '<', 'uses_limit');
            })
            ->exists();
    }

    /**
     * @param  array{period?: string, monthly_subtotal?: float, yearly_months_charged?: int}  $context
     * @return array{ok: bool, message?: string, code?: string, type?: string, value?: float, discount?: float, label?: string}
     */
    public function preview(?string $code, float $amount, array $context = []): array
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return [
                'ok' => false,
                'message' => __('main.wizard.coupon_required'),
            ];
        }

        $coupon = $this->findValid($code, $context);

        if (! $coupon) {
            return [
                'ok' => false,
                'message' => __('main.wizard.coupon_invalid'),
            ];
        }

        $discount = $this->discountAmount($coupon, $amount, $context);

        return [
            'ok' => true,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'discount' => $discount,
            'label' => $this->label($coupon),
            'message' => __('main.wizard.coupon_applied'),
        ];
    }

    /**
     * @param  array{period?: string, monthly_subtotal?: float, yearly_months_charged?: int}  $context
     */
    public function discountAmount(Coupon $coupon, float $amount, array $context = []): float
    {
        $amount = max(0, $amount);
        $base = $this->discountBase($coupon, $amount, $context);

        if ($base <= 0) {
            return 0.0;
        }

        if ($coupon->type === 'percentage') {
            $pct = min(100, max(0, (float) $coupon->value));
            $discount = round($base * ($pct / 100), 2);
        } else {
            $discount = round(min($base, max(0, (float) $coupon->value)), 2);
        }

        $cap = $coupon->max_discount;
        if ($cap !== null && $cap !== '' && (float) $cap > 0) {
            $discount = min($discount, (float) $cap);
        }

        return round(min($amount, max(0, $discount)), 2);
    }

    /**
     * @param  array{period?: string, monthly_subtotal?: float, yearly_months_charged?: int}  $context
     */
    protected function discountBase(Coupon $coupon, float $amount, array $context): float
    {
        $mode = (string) ($coupon->apply_mode ?: 'full');

        if ($mode !== 'months') {
            return $amount;
        }

        $months = max(1, (int) ($coupon->apply_months ?: 1));
        $monthly = (float) ($context['monthly_subtotal'] ?? 0);
        $period = ($context['period'] ?? 'Month') === 'Year' ? 'Year' : 'Month';
        $yearlyMonths = max(1, (int) ($context['yearly_months_charged'] ?? 10));

        if ($monthly <= 0) {
            // Fallback when monthly is unknown: treat amount as the period total.
            if ($period === 'Year') {
                $monthly = $amount / $yearlyMonths;
            } else {
                $monthly = $amount;
            }
        }

        if ($period === 'Year') {
            $months = min($months, $yearlyMonths);
        } else {
            // A monthly checkout invoice covers one month only.
            $months = min($months, 1);
        }

        return min($amount, round($monthly * $months, 2));
    }

    public function label(Coupon $coupon): string
    {
        $mode = (string) ($coupon->apply_mode ?: 'full');
        $months = (int) ($coupon->apply_months ?: 0);

        if ($coupon->type === 'percentage') {
            $value = rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.');

            if ($mode === 'months' && $months > 0) {
                return __('main.wizard.coupon_discount_percent_months', [
                    'code' => $coupon->code,
                    'value' => $value,
                    'months' => $months,
                ]);
            }

            return __('main.wizard.coupon_discount_percent', [
                'code' => $coupon->code,
                'value' => $value,
            ]);
        }

        if ($mode === 'months' && $months > 0) {
            return __('main.wizard.coupon_discount_amount_months', [
                'code' => $coupon->code,
                'value' => number_format((float) $coupon->value, 0),
                'months' => $months,
            ]);
        }

        return __('main.wizard.coupon_discount_amount', [
            'code' => $coupon->code,
            'value' => number_format((float) $coupon->value, 0),
        ]);
    }

    public function consume(Coupon $coupon): void
    {
        $coupon->increment('uses_count');
    }

    /**
     * @param  array{period?: string, monthly_subtotal?: float, yearly_months_charged?: int}  $context
     */
    public function requireValid(?string $code, array $context = []): Coupon
    {
        $coupon = $this->findValid($code, $context);

        if (! $coupon) {
            throw ValidationException::withMessages([
                'plan_config.coupon_code' => __('main.wizard.coupon_invalid'),
            ]);
        }

        return $coupon;
    }
}
