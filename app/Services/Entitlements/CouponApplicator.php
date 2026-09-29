<?php

namespace App\Services\Entitlements;

use App\Models\Coupon;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CouponApplicator
{
    public function findValid(?string $code): ?Coupon
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

        return $coupon;
    }

    /**
     * @return array{ok: bool, message?: string, code?: string, type?: string, value?: float, discount?: float, label?: string}
     */
    public function preview(?string $code, float $amount): array
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return [
                'ok' => false,
                'message' => __('main.wizard.coupon_required'),
            ];
        }

        $coupon = $this->findValid($code);

        if (! $coupon) {
            return [
                'ok' => false,
                'message' => __('main.wizard.coupon_invalid'),
            ];
        }

        $discount = $this->discountAmount($coupon, $amount);

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

    public function discountAmount(Coupon $coupon, float $amount): float
    {
        $amount = max(0, $amount);

        if ($coupon->type === 'percentage') {
            $pct = min(100, max(0, (float) $coupon->value));

            return round($amount * ($pct / 100), 2);
        }

        return round(min($amount, max(0, (float) $coupon->value)), 2);
    }

    public function label(Coupon $coupon): string
    {
        if ($coupon->type === 'percentage') {
            return __('main.wizard.coupon_discount_percent', [
                'code' => $coupon->code,
                'value' => rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.'),
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

    public function requireValid(?string $code): Coupon
    {
        $coupon = $this->findValid($code);

        if (! $coupon) {
            throw ValidationException::withMessages([
                'plan_config.coupon_code' => __('main.wizard.coupon_invalid'),
            ]);
        }

        return $coupon;
    }
}
