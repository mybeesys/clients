<?php

namespace App\Filament\Widgets;

use App\Services\Platform\PlatformDashboardService;
use Filament\Widgets\ChartWidget;

class PlatformRevenueChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    public function getHeading(): ?string
    {
        return __('main.platform_dashboard.revenue_chart');
    }

    public function getDescription(): ?string
    {
        return __('main.platform_dashboard.revenue_chart_hint');
    }

    protected function getData(): array
    {
        $series = app(PlatformDashboardService::class)->snapshot()['revenue_series'];

        return [
            'datasets' => [
                [
                    'label' => __('main.platform_dashboard.card_revenue'),
                    'data' => $series['card'],
                    'borderColor' => '#b88912',
                    'backgroundColor' => 'rgba(235, 184, 30, 0.18)',
                    'fill' => true,
                    'tension' => 0.35,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('main.platform_dashboard.new_companies'),
                    'data' => $series['companies'],
                    'borderColor' => '#166534',
                    'backgroundColor' => 'rgba(22, 101, 52, 0.12)',
                    'fill' => false,
                    'tension' => 0.35,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'position' => 'left',
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.15)'],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false],
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }
}
