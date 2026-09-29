<?php

namespace App\Filament\Resources\EntitlementProductResource\Pages;

use App\Filament\Resources\EntitlementProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEntitlementProduct extends EditRecord
{
    protected static string $resource = EntitlementProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->hidden(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
