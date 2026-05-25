<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use App\Support\TenantApplicationUrl;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    /** @var array{name?: string, url?: string|null}|null */
    public ?array $companyCreatedSuccess = null;

    public function mount(): void
    {
        parent::mount();

        $this->openCompanyCreatedModalIfNeeded();
    }

    protected function openCompanyCreatedModalIfNeeded(): void
    {
        if (filled($this->companyCreatedSuccess)) {
            return;
        }

        $payload = session()->pull('company_created_success');

        if ($payload === null) {
            $companyId = request()->integer('company_created');

            if ($companyId) {
                $company = Company::query()->find($companyId);

                if ($company) {
                    $payload = [
                        'name' => $company->name,
                        'url' => TenantApplicationUrl::forCompany($company),
                    ];
                }
            }
        }

        if ($payload === null) {
            return;
        }

        $this->companyCreatedSuccess = $payload;

        $this->mountAction('companyCreatedSuccess');
    }

    public function companyCreatedSuccessAction(): Action
    {
        return Action::make('companyCreatedSuccess')
            ->modalHeading(__('main.company_created.title'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('main.company_created.close'))
            ->modalContent(fn (): View => view(
                'filament.modals.company-created-success',
                $this->companyCreatedSuccess ?? []
            ));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
