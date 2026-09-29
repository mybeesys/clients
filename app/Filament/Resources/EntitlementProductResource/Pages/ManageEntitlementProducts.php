<?php

namespace App\Filament\Resources\EntitlementProductResource\Pages;

use App\Filament\Resources\EntitlementProductResource;
use App\Models\EntitlementSetting;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ManageEntitlementProducts extends ListRecords
{
    protected static string $resource = EntitlementProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('billing_settings')
                ->label(__('main.entitlement_billing_settings'))
                ->icon('heroicon-o-cog-6-tooth')
                ->form([
                    TextInput::make('currency')
                        ->label(__('main.entitlement_currency'))
                        ->default(fn () => (string) EntitlementSetting::getValue('currency', 'SAR'))
                        ->required()
                        ->maxLength(8),
                    TextInput::make('yearly_months_charged')
                        ->label(__('main.entitlement_yearly_months'))
                        ->helperText(__('main.entitlement_yearly_months_hint'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(12)
                        ->default(fn () => (int) EntitlementSetting::getValue('yearly_months_charged', 12))
                        ->required(),
                ])
                ->action(function (array $data): void {
                    EntitlementSetting::setValue('currency', $data['currency']);
                    EntitlementSetting::setValue('yearly_months_charged', (string) $data['yearly_months_charged']);

                    Notification::make()
                        ->title(__('main.entitlement_settings_saved'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
