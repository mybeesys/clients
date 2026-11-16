<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['apply_mode'] = $data['apply_mode'] ?? 'full';

        if (($data['apply_mode'] ?? 'full') !== 'months') {
            $data['apply_months'] = null;
        }

        if (! filled($data['max_discount'] ?? null)) {
            $data['max_discount'] = null;
        }

        if (! filled($data['applicable_period'] ?? null)) {
            $data['applicable_period'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
