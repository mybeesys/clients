<?php

namespace App\Filament\Resources\EntitlementProductResource\Pages;

use App\Filament\Resources\EntitlementProductResource;
use App\Models\EntitlementSetting;
use Filament\Actions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;

class ManageBusinessTypes extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = EntitlementProductResource::class;

    protected static string $view = 'filament.resources.entitlement-product-resource.pages.manage-business-types';

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public ?array $data = [];

    public function getTitle(): string|Htmlable
    {
        return __('main.wizard.plan_recommend_title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('main.wizard.plan_recommend_title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('main.entitlement_business_types_hint');
    }

    public function getBreadcrumb(): string
    {
        return __('main.entitlement_manage_business_types');
    }

    public function mount(): void
    {
        abort_unless(EntitlementProductResource::canViewAny(), 403);

        $this->form->fill([
            'recommendations' => $this->loadRecommendations(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(EntitlementProductResource::businessTypesFormSchema())
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

        $recommendations = collect($data['recommendations'] ?? [])
            ->filter(fn ($rec) => is_array($rec) && filled($rec['key'] ?? null))
            ->values()
            ->map(function (array $rec, int $index) {
                $quotas = [];
                foreach (['employees', 'establishments', 'screen_devices'] as $quotaKey) {
                    if (isset($rec['quotas'][$quotaKey]) && (int) $rec['quotas'][$quotaKey] > 0) {
                        $quotas[$quotaKey] = (int) $rec['quotas'][$quotaKey];
                    }
                }

                return [
                    'key' => (string) $rec['key'],
                    'name_en' => (string) ($rec['name_en'] ?? $rec['key']),
                    'name_ar' => (string) ($rec['name_ar'] ?? $rec['name_en'] ?? $rec['key']),
                    'description_en' => $rec['description_en'] ?? null,
                    'description_ar' => $rec['description_ar'] ?? null,
                    'modules' => array_values($rec['modules'] ?? []),
                    'quotas' => $quotas,
                    'sort_order' => (int) ($rec['sort_order'] ?? (($index + 1) * 10)),
                    'active' => (bool) ($rec['active'] ?? true),
                ];
            })
            ->values()
            ->all();

        EntitlementSetting::setValue('recommendations', $recommendations);
        Cache::forget('entitlement_catalog_products');
        Cache::forget('entitlement_catalog_settings');

        Notification::make()
            ->title(__('main.entitlement_business_types_saved'))
            ->success()
            ->send();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function loadRecommendations(): array
    {
        $raw = EntitlementSetting::getValue('recommendations', config('entitlements.recommendations', []));
        if (! is_array($raw) || $raw === []) {
            $raw = config('entitlements.recommendations', []);
        }

        return collect($raw)
            ->values()
            ->map(function (array $rec, int $index) {
                return [
                    'key' => $rec['key'] ?? ('type_'.($index + 1)),
                    'name_en' => $rec['name_en'] ?? '',
                    'name_ar' => $rec['name_ar'] ?? '',
                    'description_en' => $rec['description_en'] ?? null,
                    'description_ar' => $rec['description_ar'] ?? null,
                    'modules' => array_values($rec['modules'] ?? []),
                    'quotas' => [
                        'employees' => (int) ($rec['quotas']['employees'] ?? 5),
                        'establishments' => (int) ($rec['quotas']['establishments'] ?? 1),
                        'screen_devices' => (int) ($rec['quotas']['screen_devices'] ?? 0),
                    ],
                    'sort_order' => (int) ($rec['sort_order'] ?? (($index + 1) * 10)),
                    'active' => (bool) ($rec['active'] ?? true),
                ];
            })
            ->all();
    }
}
