<?php

namespace App\Services\Platform;

use App\Models\Company;
use App\Models\CompanyEntitlement;
use App\Models\HyperPayPayment;
use App\Models\PaymentSubscription;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Entitlements\EntitlementCatalog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PlatformDashboardService
{
    public function snapshot(): array
    {
        return Cache::remember('platform_dashboard.snapshot', now()->addMinutes(2), fn () => $this->buildSnapshot());
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSnapshot(): array
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $startOfYear = $now->copy()->startOfYear();
        $in14Days = $now->copy()->addDays(14);
        $in7DaysAgo = $now->copy()->subDays(7);

        $companiesTotal = Company::query()->count();
        $companiesThisMonth = Company::query()->where('created_at', '>=', $startOfMonth)->count();
        $companiesLastMonth = Company::query()
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();

        $usersTotal = User::query()->count();
        $tenantsTotal = Schema::hasTable('tenants') ? Tenant::query()->count() : 0;

        $activeSubscriptions = $this->activeSubscriptionQuery()->count();
        $expiredSubscriptions = Subscription::query()
            ->whereNull('suppressed_at')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', $now)
            ->count();
        $expiringSoon = $this->activeSubscriptionQuery()
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [$now, $in14Days])
            ->count();

        $mrr = (float) CompanyEntitlement::query()
            ->whereHas('subscription', fn ($query) => $this->constrainActive($query))
            ->sum('monthly_subtotal');

        $yearlyCount = CompanyEntitlement::query()
            ->where('period', 'Year')
            ->whereHas('subscription', fn ($query) => $this->constrainActive($query))
            ->count();
        $monthlyCount = CompanyEntitlement::query()
            ->where('period', '!=', 'Year')
            ->whereHas('subscription', fn ($query) => $this->constrainActive($query))
            ->count();

        $cardMonth = $this->hyperpayPaidBetween($startOfMonth, $now);
        $cardLastMonth = $this->hyperpayPaidBetween($startOfLastMonth, $endOfLastMonth);
        $cardYear = $this->hyperpayPaidBetween($startOfYear, $now);
        $recordedMonth = (float) PaymentSubscription::query()
            ->where('status', 'paid')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('amount');

        $failedWeek = Schema::hasTable('hyperpay_payments')
            ? HyperPayPayment::query()
                ->where('status', HyperPayPayment::STATUS_FAILED)
                ->where('created_at', '>=', $in7DaysAgo)
                ->count()
            : 0;
        $openCheckouts = Schema::hasTable('hyperpay_payments')
            ? HyperPayPayment::query()
                ->whereIn('status', [
                    HyperPayPayment::STATUS_CHECKOUT,
                    HyperPayPayment::STATUS_PENDING,
                    HyperPayPayment::STATUS_PENDING_GATEWAY,
                ])
                ->count()
            : 0;

        return [
            'companies_total' => $companiesTotal,
            'companies_this_month' => $companiesThisMonth,
            'companies_delta' => $this->deltaPercent($companiesThisMonth, $companiesLastMonth),
            'companies_spark' => $this->dailyCounts(Company::class, 7),
            'users_total' => $usersTotal,
            'tenants_total' => $tenantsTotal,
            'active_subscriptions' => $activeSubscriptions,
            'expired_subscriptions' => $expiredSubscriptions,
            'expiring_soon' => $expiringSoon,
            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'yearly_count' => $yearlyCount,
            'monthly_count' => $monthlyCount,
            'card_month' => $cardMonth,
            'card_last_month' => $cardLastMonth,
            'card_delta' => $this->deltaPercent($cardMonth, $cardLastMonth),
            'card_year' => $cardYear,
            'card_spark' => $this->dailySums(),
            'recorded_month' => $recordedMonth,
            'failed_week' => $failedWeek,
            'open_checkouts' => $openCheckouts,
            'revenue_series' => $this->monthlyRevenueSeries(),
            'module_adoption' => $this->moduleAdoption(),
        ];
    }

    /**
     * @return list<string>
     */
    public function monthLabels(): array
    {
        $labels = [];
        for ($i = 11; $i >= 0; $i--) {
            $labels[] = now()->subMonths($i)->translatedFormat('M Y');
        }

        return $labels;
    }

    protected function constrainActive($query)
    {
        return $query
            ->whereNull('suppressed_at')
            ->where(function ($inner) {
                $inner->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            });
    }

    protected function activeSubscriptionQuery()
    {
        return $this->constrainActive(Subscription::query());
    }

    protected function hyperpayPaidBetween(Carbon $from, Carbon $to): float
    {
        if (! Schema::hasTable('hyperpay_payments')) {
            return 0;
        }

        return (float) HyperPayPayment::query()
            ->where('status', HyperPayPayment::STATUS_PAID)
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');
    }

    /**
     * @return list<int>
     */
    protected function dailyCounts(string $model, int $days): array
    {
        $points = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $points[] = $model::query()
                ->whereDate('created_at', $day->toDateString())
                ->count();
        }

        return $points;
    }

    /**
     * @return list<float>
     */
    protected function dailySums(): array
    {
        if (! Schema::hasTable('hyperpay_payments')) {
            return array_fill(0, 7, 0);
        }

        $points = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $points[] = (float) HyperPayPayment::query()
                ->where('status', HyperPayPayment::STATUS_PAID)
                ->whereDate('paid_at', $day->toDateString())
                ->sum('amount');
        }

        return $points;
    }

    /**
     * @return array{labels: list<string>, card: list<float>, companies: list<int>}
     */
    protected function monthlyRevenueSeries(): array
    {
        $labels = [];
        $card = [];
        $companies = [];

        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $labels[] = $start->translatedFormat('M Y');
            $card[] = $this->hyperpayPaidBetween($start, $end);
            $companies[] = Company::query()->whereBetween('created_at', [$start, $end])->count();
        }

        return [
            'labels' => $labels,
            'card' => $card,
            'companies' => $companies,
        ];
    }

    /**
     * @return list<array{key: string, name: string, count: int}>
     */
    protected function moduleAdoption(): array
    {
        $catalog = app(EntitlementCatalog::class);
        $localeIsAr = app()->getLocale() === 'ar';
        $counts = [];

        CompanyEntitlement::query()
            ->whereHas('subscription', fn ($query) => $this->constrainActive($query))
            ->pluck('modules')
            ->each(function ($modules) use (&$counts) {
                foreach ((array) $modules as $key) {
                    if (! is_string($key) || $key === '') {
                        continue;
                    }
                    $counts[$key] = ($counts[$key] ?? 0) + 1;
                }
            });

        arsort($counts);

        $rows = [];
        foreach (array_slice($counts, 0, 8, true) as $key => $count) {
            $name = $key;
            try {
                $module = $catalog->module($key);
                $name = $localeIsAr
                    ? ($module['name_ar'] ?? $module['name_en'] ?? $key)
                    : ($module['name_en'] ?? $module['name_ar'] ?? $key);
            } catch (\Throwable) {
            }

            $rows[] = [
                'key' => $key,
                'name' => $name,
                'count' => (int) $count,
            ];
        }

        return $rows;
    }

    protected function deltaPercent(float|int $current, float|int $previous): int
    {
        if ((float) $previous <= 0) {
            return (float) $current > 0 ? 100 : 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }
}
