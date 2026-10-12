<?php

namespace App\Filament\Widgets;

use App\Services\Platform\PlatformDashboardService;
use Filament\Widgets\ChartWidget;

class PlatformModuleAdoptionChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    public function getHeading(): ?string
    {
        return __('main.platform_dashboard.modules_chart');
    }

    public function getDescription(): ?string
    {
        return __('main.platform_dashboard.modules_chart_hint');
    }

    protected function getData(): array
    {
        $rows = app(PlatformDashboardService::class)->snapshot()['module_adoption'];

        if ($rows === []) {
            return [
                'datasets' => [['data' => [0], 'backgroundColor' => ['#e2e8f0']]],
                'labels' => [__('main.platform_dashboard.no_module_data')],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => __('main.platform_dashboard.active_tenants'),
                    'data' => array_column($rows, 'count'),
                    'backgroundColor' => '#ebb81e',
                    'borderRadius' => 8,
                    'barThickness' => 18,
                ],
            ],
            'labels' => array_column($rows, 'name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.15)'],
                ],
                'y' => [
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }
}
