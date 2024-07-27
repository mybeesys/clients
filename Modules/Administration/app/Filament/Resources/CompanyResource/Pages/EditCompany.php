<?php

namespace Modules\Administration\Filament\Resources\CompanyResource\Pages;

use Modules\Administration\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\VarDumper\VarDumper;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

  


}
