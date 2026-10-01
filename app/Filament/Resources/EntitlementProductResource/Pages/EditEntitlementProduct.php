<?php

namespace App\Filament\Resources\EntitlementProductResource\Pages;

use App\Filament\Resources\EntitlementProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEntitlementProduct extends EditRecord
{
    protected static string $resource = EntitlementProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->hidden(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];

        $includes = $meta['includes'] ?? [];
        if (is_array($includes) && ! isset($includes['ar']) && ! isset($includes['en'])) {
            // Flat list from older rows → treat as Arabic (primary locale).
            $includes = ['ar' => array_values($includes), 'en' => []];
        }

        $data['meta'] = array_merge($meta, [
            'requires_any' => array_values($meta['requires_any'] ?? []),
            'grants' => array_values($meta['grants'] ?? []),
            'includes' => [
                'ar' => array_values($includes['ar'] ?? []),
                'en' => array_values($includes['en'] ?? []),
            ],
            'menu_keys' => array_values($meta['menu_keys'] ?? []),
            'api_prefixes' => array_values($meta['api_prefixes'] ?? []),
        ]);

        $data['requires'] = array_values($data['requires'] ?? []);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $existingMeta = is_array($this->record->meta) ? $this->record->meta : [];
        $incomingMeta = is_array($data['meta'] ?? null) ? $data['meta'] : [];

        $data['requires'] = array_values(array_filter($data['requires'] ?? []));

        $data['meta'] = array_merge($existingMeta, $incomingMeta, [
            'requires_any' => array_values(array_filter($incomingMeta['requires_any'] ?? [])),
            'grants' => array_values(array_filter($incomingMeta['grants'] ?? [])),
            'includes' => [
                'ar' => array_values(array_filter($incomingMeta['includes']['ar'] ?? [])),
                'en' => array_values(array_filter($incomingMeta['includes']['en'] ?? [])),
            ],
            'menu_keys' => array_values($incomingMeta['menu_keys'] ?? $existingMeta['menu_keys'] ?? []),
            'api_prefixes' => array_values($incomingMeta['api_prefixes'] ?? $existingMeta['api_prefixes'] ?? []),
        ]);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
