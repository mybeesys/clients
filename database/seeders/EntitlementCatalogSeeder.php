<?php

namespace Database\Seeders;

use App\Models\EntitlementProduct;
use App\Models\EntitlementSetting;
use Illuminate\Database\Seeder;

class EntitlementCatalogSeeder extends Seeder
{
    public function run(): void
    {
        EntitlementSetting::setValue('currency', config('entitlements.currency', 'SAR'));
        EntitlementSetting::setValue('yearly_months_charged', (string) config('entitlements.yearly_months_charged', 12));

        $platform = config('entitlements.platform', []);
        EntitlementProduct::query()->updateOrCreate(
            ['key' => $platform['key'] ?? 'platform'],
            [
                'type' => EntitlementProduct::TYPE_PLATFORM,
                'group' => null,
                'name_en' => $platform['name_en'] ?? 'Core platform',
                'name_ar' => $platform['name_ar'] ?? 'المنصة الأساسية',
                'description_en' => $platform['description_en'] ?? null,
                'description_ar' => $platform['description_ar'] ?? null,
                'price_month' => $platform['price_month'] ?? 199,
                'sort_order' => 0,
                'active' => true,
            ]
        );

        $sort = 10;
        foreach (config('entitlements.modules', []) as $key => $module) {
            EntitlementProduct::query()->updateOrCreate(
                ['key' => $key],
                [
                    'type' => EntitlementProduct::TYPE_MODULE,
                    'group' => $module['group'] ?? null,
                    'name_en' => $module['name_en'],
                    'name_ar' => $module['name_ar'],
                    'description_en' => $module['description_en'] ?? null,
                    'description_ar' => $module['description_ar'] ?? null,
                    'price_month' => $module['price_month'] ?? 0,
                    'requires' => $module['requires'] ?? [],
                    'icon' => $module['icon'] ?? null,
                    'sort_order' => $sort,
                    'active' => true,
                    'meta' => [
                        'menu_keys' => $module['menu_keys'] ?? [],
                        'api_prefixes' => $module['api_prefixes'] ?? [],
                        'requires_any' => $module['requires_any'] ?? [],
                    ],
                ]
            );
            $sort += 10;
        }

        foreach (config('entitlements.quotas', []) as $key => $quota) {
            EntitlementProduct::query()->updateOrCreate(
                ['key' => $key],
                [
                    'type' => EntitlementProduct::TYPE_QUOTA,
                    'group' => null,
                    'name_en' => $quota['name_en'],
                    'name_ar' => $quota['name_ar'],
                    'price_month' => 0,
                    'price_per_extra_month' => $quota['price_per_extra_month'] ?? 0,
                    'included' => $quota['included'] ?? 0,
                    'min' => $quota['min'] ?? 1,
                    'max' => $quota['max'] ?? 100,
                    'linked_module' => $quota['linked_module'] ?? null,
                    'sort_order' => $sort,
                    'active' => true,
                ]
            );
            $sort += 10;
        }

        // Screen devices quota (linked to digital_screens)
        EntitlementProduct::query()->updateOrCreate(
            ['key' => 'screen_devices'],
            [
                'type' => EntitlementProduct::TYPE_QUOTA,
                'group' => null,
                'name_en' => 'Screen devices',
                'name_ar' => 'أجهزة الشاشات',
                'description_en' => 'Number of digital screen devices allowed.',
                'description_ar' => 'عدد أجهزة الشاشات الرقمية المسموح بها.',
                'price_month' => 0,
                'price_per_extra_month' => 49,
                'included' => 1,
                'min' => 1,
                'max' => 200,
                'linked_module' => 'digital_screens',
                'sort_order' => $sort,
                'active' => true,
            ]
        );
    }
}
