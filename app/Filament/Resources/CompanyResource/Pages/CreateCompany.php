<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Forms\CompanyOnboardingWizard;
use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyOnboardingService;
use App\Support\TenantApplicationUrl;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\HtmlString;

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

    protected function afterCreate(): void
    {
        /** @var Company $company */
        $company = $this->getRecord();

        session()->flash('company_created_success', [
            'name' => $company->name,
            'url' => TenantApplicationUrl::forCompany($company),
        ]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        /** @var Company $company */
        $company = $this->getRecord();
        $tenantUrl = TenantApplicationUrl::forCompany($company);

        $body = $tenantUrl
            ? new HtmlString(
                e(__('main.company_created.body', ['name' => $company->name]))
                .'<br><br><span style="direction:ltr;display:inline-block;font-size:0.8125rem;">'
                .e($tenantUrl)
                .'</span>'
            )
            : __('main.company_created.body', ['name' => $company->name]);

        $notification = Notification::make()
            ->success()
            ->title(__('main.company_created.title'))
            ->body($body)
            ->persistent();

        if ($tenantUrl) {
            $notification->actions([
                NotificationAction::make('openTenant')
                    ->label(__('main.company_created.open_system'))
                    ->url($tenantUrl, shouldOpenInNewTab: true)
                    ->button(),
            ]);
        }

        return $notification;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', [
            'company_created' => $this->getRecord()->getKey(),
        ]);
    }
}
