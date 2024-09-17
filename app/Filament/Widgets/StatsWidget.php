<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;


class StatsWidget extends BaseWidget
{

    public function getHeading(): string
    {
        return __('general.states');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('New Companies', Company::count())
                ->description('New Companies that have joined.')
                ->descriptionIcon('heroicon-o-rectangle-stack', IconPosition::Before)
                ->chart([1, 3, 5, 6, 4, 7, 8])
                ->color('success'),

            Stat::make('New Subscriptions', Subscription::whereMonth('created_at',\Carbon\Carbon::now()->month)
                ->whereYear('created_at', \Carbon\Carbon::now()->year)
                ->count())
                ->description('Number of subscription this month.')
                ->descriptionIcon('heroicon-o-user-plus', IconPosition::Before)
                ->chart([1, 3, 9, 6, 7, 4, 7, 8])
                ->color('info'),

            Stat::make('New Plans', Plan::count())
                ->description('Number of plans.')
                ->descriptionIcon('heroicon-o-inbox-stack', IconPosition::Before)
                ->chart([1, 3, 2, 4, 4, 7, 8])
                ->color('primary'),
        ];
    }
}
