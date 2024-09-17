<?php

namespace App\Filament\Resources\PaymentSubscriptionResource\Pages;

use App\Filament\Resources\PaymentSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentSubscriptions extends ListRecords
{
    protected static string $resource = PaymentSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
