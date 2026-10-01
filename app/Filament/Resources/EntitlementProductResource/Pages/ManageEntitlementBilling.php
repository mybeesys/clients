<?php

namespace App\Filament\Resources\EntitlementProductResource\Pages;

use App\Filament\Resources\EntitlementProductResource;
use App\Models\EntitlementSetting;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;

class ManageEntitlementBilling extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = EntitlementProductResource::class;

    protected static string $view = 'filament.resources.entitlement-product-resource.pages.manage-entitlement-billing';

    public ?array $data = [];

    public function getTitle(): string|Htmlable
    {
        return __('main.entitlement_billing_settings');
    }

    public function getHeading(): string|Htmlable
    {
        return __('main.entitlement_billing_settings');
    }

    public function getBreadcrumb(): string
    {
        return __('main.entitlement_billing_settings');
    }

    public function mount(): void
    {
        abort_unless(EntitlementProductResource::canViewAny(), 403);

        $this->form->fill([
            'currency' => (string) EntitlementSetting::getValue('currency', 'SAR'),
            'yearly_months_charged' => (int) EntitlementSetting::getValue('yearly_months_charged', 12),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('main.entitlement_billing_settings'))
                    ->schema([
                        TextInput::make('currency')
                            ->label(__('main.entitlement_currency'))
                            ->required()
                            ->maxLength(8),
                        TextInput::make('yearly_months_charged')
                            ->label(__('main.entitlement_yearly_months'))
                            ->helperText(__('main.entitlement_yearly_months_hint'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('main.entitlement_back_to_catalog'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(EntitlementProductResource::getUrl('index')),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        EntitlementSetting::setValue('currency', $data['currency']);
        EntitlementSetting::setValue('yearly_months_charged', (string) $data['yearly_months_charged']);
        Cache::forget('entitlement_catalog_settings');

        Notification::make()
            ->title(__('main.entitlement_settings_saved'))
            ->success()
            ->send();
    }
}
