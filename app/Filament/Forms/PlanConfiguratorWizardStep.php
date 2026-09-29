<?php

namespace App\Filament\Forms;

use App\Services\Entitlements\EntitlementCatalog;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard;

class PlanConfiguratorWizardStep
{
    public static function make(bool $required = true): Wizard\Step
    {
        return Wizard\Step::make('plan')
            ->label(__('main.wizard.plan_information'))
            ->icon('heroicon-o-squares-plus')
            ->schema(static::schema($required));
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function schema(bool $required = true): array
    {
        $catalog = app(EntitlementCatalog::class);
        $employeesIncluded = (int) ($catalog->quota('employees')['included'] ?? 5);
        $establishmentsIncluded = (int) ($catalog->quota('establishments')['included'] ?? 1);
        $screenDevicesIncluded = (int) ($catalog->quota('screen_devices')['included'] ?? 1);

        return [
            ViewField::make('plan_configurator')
                ->dehydrated(false)
                ->columnSpanFull()
                ->view('filament.forms.plan-configurator')
                ->viewData([
                    'catalog' => $catalog->toFrontend(),
                    'required' => $required,
                ]),
            Hidden::make('plan_config')
                ->default([
                    'period' => 'Month',
                    'modules' => [],
                    'employees' => $employeesIncluded,
                    'establishments' => $establishmentsIncluded,
                    'screen_devices' => $screenDevicesIncluded,
                    'coupon_code' => null,
                ])
                ->dehydrated()
                ->required($required)
                ->rule($required ? 'array' : 'nullable')
                ->rule(fn () => function (string $attribute, $value, \Closure $fail) use ($required, $catalog) {
                    if (! $required) {
                        return;
                    }

                    $modules = is_array($value) ? ($value['modules'] ?? []) : [];

                    if (! is_array($modules) || $modules === []) {
                        $fail(__('main.wizard.plan_modules_required'));

                        return;
                    }

                    $resolved = $catalog->resolveDependencies($modules);
                    if ($catalog->missingRequiresAny($resolved) !== []) {
                        $fail(__('main.wizard.plan_reports_needs_data_module'));
                    }
                }),
        ];
    }
}
