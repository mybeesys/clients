<?php

namespace Modules\Administration\Filament\Resources\FeatureResource\Pages;

use Modules\Administration\Filament\Resources\FeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeatures extends ListRecords
{
    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
