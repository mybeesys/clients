<?php

namespace Modules\Administration\Filament\Resources\CompanyResource\Pages;

use App\Models\User;
use Modules\Administration\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
    }
}
