<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use App\Models\ReferralProgramSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ReferralProgramSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static string $view = 'filament.pages.referral-program-settings';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('referrals.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('referrals.settings');
    }

    public function getTitle(): string
    {
        return __('referrals.settings');
    }

    public function mount(): void
    {
        $settings = ReferralProgramSetting::current();

        $this->form->fill([
            'is_enabled' => $settings->is_enabled,
            'default_points_per_conversion' => $settings->default_points_per_conversion,
            'monthly_points_cap' => $settings->monthly_points_cap,
            'promotional_template_ar' => $settings->promotional_template_ar,
            'promotional_template_en' => $settings->promotional_template_en,
            'points_by_plan' => collect($settings->points_by_plan ?? [])
                ->map(fn ($points, $planId) => ['plan_id' => (int) $planId, 'points' => (int) $points])
                ->values()
                ->all(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('referrals.settings'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('referrals.fields.enabled'))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('default_points_per_conversion')
                            ->label(__('referrals.fields.default_points'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('monthly_points_cap')
                            ->label(__('referrals.fields.monthly_cap'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        Forms\Components\Textarea::make('promotional_template_ar')
                            ->label(__('referrals.fields.template_ar'))
                            ->helperText('{link} {name} {code}')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('promotional_template_en')
                            ->label(__('referrals.fields.template_en'))
                            ->helperText('{link} {name} {code}')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('points_by_plan')
                            ->label(__('referrals.fields.points_by_plan'))
                            ->schema([
                                Forms\Components\Select::make('plan_id')
                                    ->label(__('referrals.fields.plan'))
                                    ->options(fn () => Plan::query()->where('active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                                Forms\Components\TextInput::make('points')
                                    ->label(__('referrals.fields.points_awarded'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = ReferralProgramSetting::current();

        $pointsByPlan = collect($data['points_by_plan'] ?? [])
            ->filter(fn (array $row) => filled($row['plan_id'] ?? null))
            ->mapWithKeys(fn (array $row) => [(string) $row['plan_id'] => (int) $row['points']])
            ->all();

        $settings->update([
            'is_enabled' => (bool) ($data['is_enabled'] ?? false),
            'default_points_per_conversion' => (int) ($data['default_points_per_conversion'] ?? 0),
            'monthly_points_cap' => filled($data['monthly_points_cap'] ?? null) ? (int) $data['monthly_points_cap'] : null,
            'promotional_template_ar' => $data['promotional_template_ar'] ?? null,
            'promotional_template_en' => $data['promotional_template_en'] ?? null,
            'points_by_plan' => $pointsByPlan,
        ]);

        Notification::make()
            ->title(__('general.save'))
            ->success()
            ->send();
    }
}
