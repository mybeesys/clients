<?php

namespace App\Filament\Widgets;

use App\Services\ReferralStatsService;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReferralOverviewStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = app(ReferralStatsService::class)->overview();

        return [
            Stat::make(__('referrals.stats.referrers'), $stats['referrers'])
                ->description(__('referrals.stats.referrers_desc'))
                ->descriptionIcon('heroicon-o-users', IconPosition::Before)
                ->color('primary'),

            Stat::make(__('referrals.stats.invitations'), $stats['invitations'])
                ->description(__('referrals.stats.invitations_desc'))
                ->descriptionIcon('heroicon-o-paper-airplane', IconPosition::Before)
                ->color('info'),

            Stat::make(__('referrals.stats.distinct_visits'), $stats['distinct_visits'])
                ->description(__('referrals.stats.visits_total', ['count' => $stats['visits']]))
                ->descriptionIcon('heroicon-o-eye', IconPosition::Before)
                ->color('warning'),

            Stat::make(__('referrals.stats.confirmed_conversions'), $stats['confirmed_conversions'])
                ->description(__('referrals.stats.conversion_rate', ['rate' => $stats['conversion_rate']]))
                ->descriptionIcon('heroicon-o-check-badge', IconPosition::Before)
                ->color('success'),

            Stat::make(__('referrals.stats.total_points'), $stats['total_points'])
                ->description(__('referrals.stats.total_points_desc'))
                ->descriptionIcon('heroicon-o-star', IconPosition::Before)
                ->color('gray'),
        ];
    }
}
