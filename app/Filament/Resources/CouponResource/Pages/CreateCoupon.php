<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['apply_mode'] = $data['apply_mode'] ?? 'full';
        $data['uses_count'] = (int) ($data['uses_count'] ?? 0);

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
