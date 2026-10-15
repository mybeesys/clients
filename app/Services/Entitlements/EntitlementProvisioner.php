<?php

namespace App\Services\Entitlements;

use App\Models\Company;
use App\Models\CompanyEntitlement;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;

class EntitlementProvisioner
{
    public function __construct(
        protected EntitlementPricer $pricer,
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
     *     coupon_code?: string|null,
     *     started_at?: mixed,
     *     expired_at?: mixed,
     *     grace_days_ended_at?: mixed
     * }  $input
     */
    public function provision(Company $company, array $input): CompanyEntitlement
    {
        $quote = $this->pricer->quote($input);
        $plan = $this->ensureCustomPlan($quote);

        $startedAt = isset($input['started_at'])
            ? Carbon::parse($input['started_at'])
            : now();

        $expiredAt = isset($input['expired_at'])
            ? Carbon::parse($input['expired_at'])
            : (
                $quote['period'] === 'Year'
                    ? $startedAt->copy()->addYear()
                    : $startedAt->copy()->addMonth()
            );

        $this->suppressActiveSubscriptions($company);

        $subscription = Subscription::create([
            'plan_id' => $plan->id,
            'started_at' => $startedAt,
            'expired_at' => $expiredAt,
            'grace_days_ended_at' => $input['grace_days_ended_at'] ?? null,
            'subscriber_type' => Company::class,
            'subscriber_id' => $company->id,
        ]);

        if (! empty($quote['coupon_id'])) {
            $coupon = \App\Models\Coupon::query()->find($quote['coupon_id']);
            if ($coupon) {
                $this->coupons->consume($coupon);
            }
        }

        $entitlement = CompanyEntitlement::query()->updateOrCreate(
            ['company_id' => $company->id],
            [
                'subscription_id' => $subscription->id,
                'modules' => $quote['modules'],
                'employees_quota' => $quote['employees'],
                'establishments_quota' => $quote['establishments'],
                'screen_devices_quota' => $quote['screen_devices'],
                'pos_devices_quota' => $quote['pos_devices'] ?? 0,
                'waiter_seats_quota' => $quote['waiter_seats'] ?? 0,
                'kitchen_devices_quota' => $quote['kitchen_devices'] ?? 0,
                'period' => $quote['period'],
                'currency' => $quote['currency'],
                'monthly_subtotal' => $quote['monthly_subtotal'],
                'period_total' => $quote['period_total'],
                'line_items' => $quote['line_items'],
                'meta' => [
                    'yearly_months_charged' => $quote['yearly_months_charged'],
                    'period_subtotal' => $quote['period_subtotal'],
                    'discount' => $quote['discount'],
                    'coupon_code' => $quote['coupon_code'],
                    'coupon_id' => $quote['coupon_id'],
                    'catalog_version' => 1,
                ],
            ]
        );

        Cache::forget("tenant_subscription_status:{$company->id}");
        Cache::forget("tenant_entitlements:{$company->id}");

        return $entitlement;
    }

    /**
     * Renew the company's current entitlement package for another period.
     * Remaining time is preserved by extending from the later of now / current expiry.
     *
     * @param  array{coupon_code?: string|null, period?: string}  $overrides
     */
    public function renew(Company $company, array $overrides = []): CompanyEntitlement
    {
        $entitlement = $company->entitlement;

        if (! $entitlement) {
            throw ValidationException::withMessages([
                'renew' => __('main.subscribe.renew_requires_custom_package'),
            ]);
        }

        $current = $company->subscription;
        $baseExpiry = now();

        if ($current?->expired_at) {
            $currentExpiry = Carbon::parse($current->expired_at);
            if ($currentExpiry->greaterThan($baseExpiry)) {
                $baseExpiry = $currentExpiry;
            }
        }

        $period = ($overrides['period'] ?? $entitlement->period) === 'Year' ? 'Year' : 'Month';

        return $this->provision($company, [
            'period' => $period,
            'modules' => $entitlement->modules ?? [],
            'employees' => (int) $entitlement->employees_quota,
            'establishments' => (int) $entitlement->establishments_quota,
            'screen_devices' => (int) $entitlement->screen_devices_quota,
            'pos_devices' => (int) ($entitlement->pos_devices_quota ?? 0),
            'waiter_seats' => (int) ($entitlement->waiter_seats_quota ?? 0),
            'kitchen_devices' => (int) ($entitlement->kitchen_devices_quota ?? 0),
            'coupon_code' => $overrides['coupon_code'] ?? null,
            'started_at' => now(),
            'expired_at' => $period === 'Year'
                ? $baseExpiry->copy()->addYear()
                : $baseExpiry->copy()->addMonth(),
        ]);
    }

    /**
     * Replace the package with a newly configured build.
     *
     * @param  array{
     *     period?: string,
     *     modules?: list<string>,
     *     employees?: int,
     *     establishments?: int,
     *     screen_devices?: int,
     *     coupon_code?: string|null
     * }  $input
     */
    public function replace(Company $company, array $input): CompanyEntitlement
    {
        return $this->provision($company, $input);
    }

    /**
     * Mid-cycle quota top-up after payment. Updates quotas only — keeps modules and expiry.
     *
     * @param  array{
     *     employees?: int,
     *     establishments?: int,
     *     screen_devices?: int,
     *     pos_devices?: int,
     *     waiter_seats?: int,
     *     kitchen_devices?: int
     * }  $targetQuotas
     */
    public function applyAddon(Company $company, array $targetQuotas): CompanyEntitlement
    {
        $quote = $this->pricer->quoteAddon($company, $targetQuotas);
        $entitlement = $company->entitlement;

        if (! $entitlement) {
            throw ValidationException::withMessages([
                'addon' => __('main.subscribe.addon_requires_package'),
            ]);
        }

        $meta = is_array($entitlement->meta) ? $entitlement->meta : [];
        $meta['last_addon'] = [
            'at' => now()->toIso8601String(),
            'period_total' => $quote['period_total'],
            'remaining_months' => $quote['remaining_months'],
            'deltas' => $quote['deltas'],
            'line_items' => $quote['line_items'],
            'currency' => $quote['currency'],
        ];

        $entitlement->forceFill([
            'employees_quota' => $quote['employees'],
            'establishments_quota' => $quote['establishments'],
            'screen_devices_quota' => $quote['screen_devices'],
            'pos_devices_quota' => $quote['pos_devices'],
            'waiter_seats_quota' => $quote['waiter_seats'],
            'kitchen_devices_quota' => $quote['kitchen_devices'],
            'meta' => $meta,
        ])->save();

        Cache::forget("tenant_subscription_status:{$company->id}");
        Cache::forget("tenant_entitlements:{$company->id}");

        return $entitlement->fresh();
    }

    protected function suppressActiveSubscriptions(Company $company): void
    {
        Subscription::query()
            ->where('subscriber_type', Company::class)
            ->where('subscriber_id', $company->id)
            ->whereNull('suppressed_at')
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->update([
                'suppressed_at' => now(),
                'was_switched' => true,
            ]);
    }

    protected function ensureCustomPlan(array $quote): Plan
    {
        $config = config('entitlements.custom_plan');

        $plan = Plan::query()->firstOrCreate(
            ['name' => $config['name']],
            [
                'name_ar' => $config['name_ar'],
                'description' => $config['description'],
                'description_ar' => $config['description_ar'],
                'periodicity' => 1,
                'periodicity_type' => $quote['period'] === 'Year'
                    ? PeriodicityType::Year
                    : PeriodicityType::Month,
                'price' => $quote['period_total'],
                'price_after_discount' => $quote['period_total'],
                'grace_days' => 0,
                'active' => true,
            ]
        );

        $plan->forceFill([
            'periodicity_type' => $quote['period'] === 'Year'
                ? PeriodicityType::Year
                : PeriodicityType::Month,
            'price' => $quote['period_total'],
            'price_after_discount' => $quote['period_total'],
            'active' => true,
        ])->save();

        return $plan;
    }
}
