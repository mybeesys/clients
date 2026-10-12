<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\SubscriptionResource;
use App\Filament\Resources\UserResource;
use App\Services\Platform\PlatformDashboardService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverviewStats extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $data = app(PlatformDashboardService::class)->snapshot();
        $sar = __('general.sar');

        return [
            Stat::make(__('main.platform_dashboard.companies'), number_format($data['companies_total']))
                ->description($this->deltaLabel($data['companies_delta'], __('main.platform_dashboard.this_month', ['count' => $data['companies_this_month']])))
                ->descriptionIcon($data['companies_delta'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($data['companies_spark'])
                ->color($data['companies_delta'] >= 0 ? 'success' : 'danger')
                ->url(CompanyResource::getUrl()),

            Stat::make(__('main.platform_dashboard.active_subscriptions'), number_format($data['active_subscriptions']))
                ->description(__('main.platform_dashboard.expired_count', ['count' => $data['expired_subscriptions']]))
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info')
                ->url(SubscriptionResource::getUrl()),

            Stat::make(__('main.platform_dashboard.mrr'), number_format($data['mrr'], 0).' '.$sar)
                ->description(__('main.platform_dashboard.arr', ['amount' => number_format($data['arr'], 0).' '.$sar]))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make(__('main.platform_dashboard.users'), number_format($data['users_total']))
                ->description(__('main.platform_dashboard.tenants', ['count' => $data['tenants_total']]))
                ->descriptionIcon('heroicon-m-users')
                ->color('gray')
                ->url(UserResource::getUrl()),
        ];
    }

    protected function deltaLabel(int $delta, string $fallback): string
    {
        $sign = $delta > 0 ? '+' : '';

        return $fallback.' · '.$sign.$delta.'%';
    }
}
