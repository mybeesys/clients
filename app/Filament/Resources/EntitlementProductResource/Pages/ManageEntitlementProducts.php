<?php

namespace App\Filament\Resources\EntitlementProductResource\Pages;

use App\Filament\Resources\EntitlementProductResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;

class ManageEntitlementProducts extends ListRecords
{
    protected static string $resource = EntitlementProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('business_types')
                ->label(__('main.entitlement_manage_business_types'))
                ->icon('heroicon-o-building-storefront')
                ->url(EntitlementProductResource::getUrl('business-types')),

            Actions\Action::make('billing_settings')
                ->label(__('main.entitlement_billing_settings'))
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->url(EntitlementProductResource::getUrl('billing')),

            Actions\Action::make('resync_catalog')
                ->label(__('main.entitlement_resync_catalog'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading(__('main.entitlement_resync_catalog'))
                ->modalDescription(__('main.entitlement_resync_catalog_hint'))
                ->modalSubmitActionLabel(__('main.entitlement_resync_catalog'))
                ->action(function (): void {
                    (new \Database\Seeders\EntitlementCatalogSeeder)->run();
                    Cache::forget('entitlement_catalog_products');
                    Cache::forget('entitlement_catalog_settings');

                    Notification::make()
                        ->title(__('main.entitlement_resync_done'))
                        ->success()
                        ->send();

                    $this->redirect(EntitlementProductResource::getUrl('index'), navigate: true);
                }),
        ];
    }
}
