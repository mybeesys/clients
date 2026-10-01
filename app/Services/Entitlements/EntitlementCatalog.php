<?php

namespace App\Services\Entitlements;

use App\Models\EntitlementProduct;
use App\Models\EntitlementSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class EntitlementCatalog
{
    public function currency(): string
    {
        return (string) $this->setting('currency', config('entitlements.currency', 'SAR'));
    }

    public function yearlyMonthsCharged(): int
    {
        return (int) $this->setting('yearly_months_charged', config('entitlements.yearly_months_charged', 12));
    }

    public function platform(): array
    {
        $fromDb = $this->products()
            ->firstWhere('type', EntitlementProduct::TYPE_PLATFORM);

        if ($fromDb) {
            return $fromDb;
        }

        return (array) config('entitlements.platform', []);
    }

    /**
     * @return array<string, array>
     */
    public function quotas(): array
    {
        $fromDb = $this->products()
            ->where('type', EntitlementProduct::TYPE_QUOTA)
            ->keyBy('key')
            ->all();

        if ($fromDb !== []) {
            return $fromDb;
        }

        return (array) config('entitlements.quotas', []);
    }

    public function quota(string $key): ?array
    {
        return $this->quotas()[$key] ?? null;
    }

    public function groups(): array
    {
        return (array) config('entitlements.groups', []);
    }

    public function modules(): Collection
    {
        $fromDb = $this->products()
            ->where('type', EntitlementProduct::TYPE_MODULE)
            ->values();

        if ($fromDb->isNotEmpty()) {
            return $fromDb->keyBy('key');
        }

        return collect(config('entitlements.modules', []));
    }

    public function module(string $key): array
    {
        $module = $this->modules()->get($key);

        if (! is_array($module)) {
            throw new InvalidArgumentException("Unknown entitlement module [{$key}].");
        }

        return $module;
    }

    public function hasModule(string $key): bool
    {
        return $this->modules()->has($key);
    }

    /**
     * @param  list<string>  $selected
     * @return list<string>
     */
    public function resolveDependencies(array $selected): array
    {
        $resolved = [];
        $queue = array_values(array_unique(array_filter($selected)));

        while ($queue !== []) {
            $key = array_shift($queue);

            if (! $this->hasModule($key) || in_array($key, $resolved, true)) {
                continue;
            }

            $resolved[] = $key;

            foreach ($this->module($key)['requires'] ?? [] as $required) {
                if (! in_array($required, $resolved, true)) {
                    $queue[] = $required;
                }
            }
        }

        sort($resolved);

        return $resolved;
    }

    /**
     * Soft dependency: module needs at least one of requires_any in the selection.
     *
     * @param  list<string>  $modules
     * @return list<string> module keys that fail the check
     */
    public function missingRequiresAny(array $modules): array
    {
        $failing = [];

        foreach ($modules as $key) {
            if (! $this->hasModule($key)) {
                continue;
            }

            $any = $this->moduleRequiresAny($key);

            if ($any === []) {
                continue;
            }

            foreach ($any as $candidate) {
                if (in_array($candidate, $modules, true)) {
                    continue 2;
                }
            }

            $failing[] = $key;
        }

        return $failing;
    }

    /**
     * @return list<string>
     */
    public function moduleRequiresAny(string $key): array
    {
        if (! $this->hasModule($key)) {
            return [];
        }

        $fromModule = $this->module($key)['requires_any'] ?? null;

        if (is_array($fromModule) && $fromModule !== []) {
            return array_values($fromModule);
        }

        $fromConfig = config("entitlements.modules.{$key}.requires_any", []);

        return is_array($fromConfig) ? array_values($fromConfig) : [];
    }

    public function toFrontend(): array
    {
        $localeIsAr = app()->getLocale() === 'ar';
        $platform = $this->platform();

        return [
            'currency' => $this->currency(),
            'yearly_months_charged' => $this->yearlyMonthsCharged(),
            'platform' => [
                'key' => $platform['key'] ?? 'platform',
                'name' => $localeIsAr
                    ? ($platform['name_ar'] ?? $platform['name_en'])
                    : ($platform['name_en'] ?? $platform['name_ar']),
                'description' => $localeIsAr
                    ? ($platform['description_ar'] ?? $platform['description_en'] ?? null)
                    : ($platform['description_en'] ?? $platform['description_ar'] ?? null),
                'price_month' => (float) ($platform['price_month'] ?? 0),
            ],
            'recommendations' => $this->recommendationsForFrontend($localeIsAr),
            'quotas' => collect($this->quotas())->map(function (array $quota) use ($localeIsAr) {
                return [
                    'key' => $quota['key'],
                    'name' => $localeIsAr ? ($quota['name_ar'] ?? $quota['name_en']) : ($quota['name_en'] ?? $quota['name_ar']),
                    'min' => (int) ($quota['min'] ?? 1),
                    'max' => (int) ($quota['max'] ?? 100),
                    'included' => (int) ($quota['included'] ?? 0),
                    'price_per_extra_month' => (float) ($quota['price_per_extra_month'] ?? 0),
                    'linked_module' => $quota['linked_module'] ?? null,
                ];
            })->values()->all(),
            'groups' => collect($this->groups())->map(function (array $group, string $key) use ($localeIsAr) {
                return [
                    'key' => $key,
                    'name' => $localeIsAr ? ($group['name_ar'] ?? $group['name_en']) : ($group['name_en'] ?? $group['name_ar']),
                ];
            })->values()->all(),
            'modules' => $this->modules()->map(function (array $module) use ($localeIsAr) {
                $key = $module['key'];

                return [
                    'key' => $key,
                    'group' => $module['group'],
                    'name' => $localeIsAr ? ($module['name_ar'] ?? $module['name_en']) : ($module['name_en'] ?? $module['name_ar']),
                    'description' => $localeIsAr
                        ? ($module['description_ar'] ?? $module['description_en'] ?? null)
                        : ($module['description_en'] ?? $module['description_ar'] ?? null),
                    'price_month' => (float) ($module['price_month'] ?? 0),
                    'requires' => array_values($module['requires'] ?? []),
                    'requires_any' => $this->moduleRequiresAny($key),
                    'grants' => array_values($module['grants'] ?? $module['meta']['grants'] ?? []),
                    'includes' => $this->moduleIncludes($module, $localeIsAr),
                    'icon' => $module['icon'] ?? null,
                ];
            })->values()->all(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function recommendationsForFrontend(bool $localeIsAr): array
    {
        $raw = $this->setting('recommendations', config('entitlements.recommendations', []));
        if (! is_array($raw) || $raw === []) {
            $raw = config('entitlements.recommendations', []);
        }

        return collect($raw)
            ->filter(fn ($rec) => is_array($rec) && ($rec['active'] ?? true))
            ->sortBy(fn ($rec) => (int) ($rec['sort_order'] ?? 100))
            ->map(function (array $rec) use ($localeIsAr) {
                return [
                    'key' => $rec['key'],
                    'name' => $localeIsAr ? ($rec['name_ar'] ?? $rec['name_en']) : ($rec['name_en'] ?? $rec['name_ar']),
                    'description' => $localeIsAr
                        ? ($rec['description_ar'] ?? $rec['description_en'] ?? null)
                        : ($rec['description_en'] ?? $rec['description_ar'] ?? null),
                    'modules' => array_values($rec['modules'] ?? []),
                    'quotas' => $rec['quotas'] ?? [],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $module
     * @return list<string>
     */
    protected function moduleIncludes(array $module, bool $localeIsAr): array
    {
        $includes = $module['includes'] ?? $module['meta']['includes'] ?? [];
        if (! is_array($includes) || $includes === []) {
            return [];
        }

        if (isset($includes['ar']) || isset($includes['en'])) {
            $list = $localeIsAr
                ? ($includes['ar'] ?? $includes['en'] ?? [])
                : ($includes['en'] ?? $includes['ar'] ?? []);

            return array_values(array_filter(array_map('strval', is_array($list) ? $list : [])));
        }

        return array_values(array_filter(array_map('strval', $includes)));
    }

    /**
     * @return Collection<int, array>
     */
    protected function products(): Collection
    {
        if (! $this->tableReady('entitlement_products')) {
            return collect();
        }

        return Cache::remember('entitlement_catalog_products', now()->addMinutes(30), function () {
            return EntitlementProduct::query()
                ->active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (EntitlementProduct $product) => $product->toCatalogArray());
        });
    }

    protected function setting(string $key, mixed $default = null): mixed
    {
        if (! $this->tableReady('entitlement_settings')) {
            return $default;
        }

        return EntitlementSetting::getValue($key, $default);
    }

    protected function tableReady(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
