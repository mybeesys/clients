<?php

namespace App\Filament\Resources\PaymentSubscriptionResource\Pages;

use App\Filament\Resources\PaymentSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentSubscription extends EditRecord
{
    protected static string $resource = PaymentSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
