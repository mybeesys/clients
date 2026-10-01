<?php

namespace Database\Seeders;

use App\Models\EntitlementProduct;
use App\Models\EntitlementSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class EntitlementCatalogSeeder extends Seeder
{
    public function run(): void
    {
        EntitlementSetting::setValue('currency', config('entitlements.currency', 'SAR'));
        EntitlementSetting::setValue('yearly_months_charged', (string) config('entitlements.yearly_months_charged', 12));
        EntitlementSetting::setValue('recommendations', config('entitlements.recommendations', []));

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
                'meta' => [],
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
                        'grants' => $module['grants'] ?? [],
                        'includes' => $module['includes'] ?? [],
                    ],
                ]
            );
            $sort += 10;
        }

        // Keep legacy keys for old subscriptions but hide them from the builder.
        foreach (config('entitlements.legacy_modules', []) as $legacyKey) {
            EntitlementProduct::query()
                ->where('key', $legacyKey)
                ->where('type', EntitlementProduct::TYPE_MODULE)
                ->update(['active' => false]);
        }

        foreach (config('entitlements.quotas', []) as $key => $quota) {
            EntitlementProduct::query()->updateOrCreate(
                ['key' => $key],
                [
                    'type' => EntitlementProduct::TYPE_QUOTA,
                    'group' => null,
                    'name_en' => $quota['name_en'],
                    'name_ar' => $quota['name_ar'],
                    'description_en' => $quota['description_en'] ?? null,
                    'description_ar' => $quota['description_ar'] ?? null,
                    'price_month' => 0,
                    'price_per_extra_month' => $quota['price_per_extra_month'] ?? 0,
                    'included' => $quota['included'] ?? 0,
                    'min' => $quota['min'] ?? 1,
                    'max' => $quota['max'] ?? 100,
                    'linked_module' => $quota['linked_module'] ?? null,
                    'sort_order' => $sort,
                    'active' => true,
                    'meta' => [],
                ]
            );
            $sort += 10;
        }

        Cache::forget('entitlement_catalog_products');
        Cache::forget('entitlement_catalog_settings');
    }
}
