<?php

namespace App\Filament\Resources\ReferralCodeResource\Pages;

use App\Filament\Resources\ReferralCodeResource;
use App\Services\ReferralService;
use Filament\Resources\Pages\CreateRecord;

class CreateReferralCode extends CreateRecord
{
    protected static string $resource = ReferralCodeResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(ReferralService::class)->createCode($data);
    }
}
