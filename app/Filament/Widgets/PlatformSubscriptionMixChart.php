<?php

namespace App\Filament\Widgets;

use App\Services\Platform\PlatformDashboardService;
use Filament\Widgets\ChartWidget;

class PlatformSubscriptionMixChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    public function getHeading(): ?string
    {
        return __('main.platform_dashboard.mix_chart');
    }

    public function getDescription(): ?string
    {
        return __('main.platform_dashboard.mix_chart_hint');
    }

    protected function getData(): array
    {
        $data = app(PlatformDashboardService::class)->snapshot();

        return [
            'datasets' => [
                [
                    'data' => [
                        $data['yearly_count'],
                        $data['monthly_count'],
                    ],
                    'backgroundColor' => ['#ebb81e', '#1e293b'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => [
                __('general.annual'),
                __('general.monthly'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '64%',
        ];
    }
}
