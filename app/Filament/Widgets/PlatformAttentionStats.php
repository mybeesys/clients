<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\HyperPayPaymentResource;
use App\Filament\Resources\SubscriptionResource;
use App\Services\Platform\PlatformDashboardService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformAttentionStats extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $data = app(PlatformDashboardService::class)->snapshot();
        $sar = __('general.sar');

        return [
            Stat::make(__('main.platform_dashboard.card_revenue_month'), number_format($data['card_month'], 0).' '.$sar)
                ->description($this->deltaLabel($data['card_delta'], __('main.platform_dashboard.card_revenue_year', ['amount' => number_format($data['card_year'], 0).' '.$sar])))
                ->descriptionIcon($data['card_delta'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($data['card_spark'])
                ->color('success')
                ->url(HyperPayPaymentResource::getUrl()),

            Stat::make(__('main.platform_dashboard.expiring_soon'), number_format($data['expiring_soon']))
                ->description(__('main.platform_dashboard.expiring_soon_hint'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($data['expiring_soon'] > 0 ? 'warning' : 'success')
                ->url(SubscriptionResource::getUrl()),

            Stat::make(__('main.platform_dashboard.failed_payments'), number_format($data['failed_week']))
                ->description(__('main.platform_dashboard.failed_payments_hint'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($data['failed_week'] > 0 ? 'danger' : 'gray')
                ->url(HyperPayPaymentResource::getUrl()),

            Stat::make(__('main.platform_dashboard.open_checkouts'), number_format($data['open_checkouts']))
                ->description(__('main.platform_dashboard.open_checkouts_hint'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($data['open_checkouts'] > 0 ? 'info' : 'gray')
                ->url(HyperPayPaymentResource::getUrl()),
        ];
    }

    protected function deltaLabel(int $delta, string $fallback): string
    {
        $sign = $delta > 0 ? '+' : '';

        return $fallback.' · '.$sign.$delta.'%';
    }
}
