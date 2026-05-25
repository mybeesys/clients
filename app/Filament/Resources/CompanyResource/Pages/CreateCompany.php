<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Forms\CompanyOnboardingWizard;
use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyOnboardingService;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected ?string $maxContentWidth = '7xl';

    public function form(Form $form): Form
    {
        return CompanyOnboardingWizard::configure($form);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function handleRecordCreation(array $data): Company
    {
        return app(CompanyOnboardingService::class)->create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
