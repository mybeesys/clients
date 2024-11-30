<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Settings extends Cluster
{
    protected static ?string $navigationIcon = '';

 
    public static function getNavigationGroup(): ?string
    {
        return __('main.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.system_settings');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('main.settings');
    }
}
