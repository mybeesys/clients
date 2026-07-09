<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ReferralLeaderboardWidget;
use App\Filament\Widgets\ReferralOverviewStats;
use Filament\Pages\Page;
use Filament\Widgets\WidgetConfiguration;

class ReferralDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static string $view = 'filament-panels::pages.dashboard';

    protected static ?int $navigationSort = 0;

    public static function getNavigationGroup(): ?string
    {
        return __('referrals.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('referrals.dashboard');
    }

    public function getTitle(): string
    {
        return __('referrals.dashboard');
    }

    /**
     * @return array<class-string<\Filament\Widgets\Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            ReferralOverviewStats::class,
            ReferralLeaderboardWidget::class,
        ];
    }

    /**
     * @return array<class-string<\Filament\Widgets\Widget> | WidgetConfiguration>
     */
    public function getVisibleWidgets(): array
    {
        return $this->filterVisibleWidgets($this->getWidgets());
    }

    /**
     * @return int|string|array<string, int|string|null>
     */
    public function getColumns(): int | string | array
    {
        return 2;
    }
}
