<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PlatformAttentionStats;
use App\Filament\Widgets\PlatformExpiringSubscriptionsWidget;
use App\Filament\Widgets\PlatformModuleAdoptionChart;
use App\Filament\Widgets\PlatformOverviewStats;
use App\Filament\Widgets\PlatformRecentCompaniesWidget;
use App\Filament\Widgets\PlatformRevenueChart;
use App\Filament\Widgets\PlatformSubscriptionMixChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = -2;

    public function getTitle(): string
    {
        return __('main.platform_dashboard.title');
    }

    public function getHeading(): string
    {
        return __('main.platform_dashboard.heading');
    }

    public function getSubheading(): ?string
    {
        return __('main.platform_dashboard.subheading');
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            PlatformOverviewStats::class,
            PlatformAttentionStats::class,
            PlatformRevenueChart::class,
            PlatformSubscriptionMixChart::class,
            PlatformModuleAdoptionChart::class,
            PlatformExpiringSubscriptionsWidget::class,
            PlatformRecentCompaniesWidget::class,
        ];
    }

    /**
     * @return int|string|array<string, int|string|null>
     */
    public function getColumns(): int|string|array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }
}
