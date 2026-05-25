<?php

namespace App\Filament\Forms;

use App\Models\Plan;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Get;

class SubscriptionWizardStep
{
    public static function make(bool $planRequired = true): Wizard\Step
    {
        return Wizard\Step::make('subscription')
            ->label(__('main.wizard.subscription_information'))
            ->icon('heroicon-o-credit-card')
            ->schema(static::schema($planRequired));
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function schema(bool $planRequired = true): array
    {
        $planField = Hidden::make('subscription.plan_id')->dehydrated();

        if ($planRequired) {
            $planField->required();
        }

        return [
            ViewField::make('subscription_plan_picker')
                ->dehydrated(false)
                ->columnSpanFull()
                ->view('filament.forms.subscription-plan-picker')
                ->viewData([
                    'plans' => Plan::query()
                        ->active()
                        ->withCount('feature_plans')
                        ->orderBy('price')
                        ->get(),
                    'planRequired' => $planRequired,
                ]),
            $planField,
            Section::make(__('main.wizard.subscription_period'))
                ->description(__('main.wizard.subscription_period_hint'))
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->collapsible()
                ->collapsed(false)
                ->schema([
                    DateTimePicker::make('subscription.started_at')
                        ->label(__('fields.start_date'))
                        ->default(now())
                        ->native(false)
                        ->displayFormat('d/m/Y H:i')
                        ->required(fn (Get $get): bool => filled($get('subscription.plan_id'))),
                    DateTimePicker::make('subscription.expired_at')
                        ->label(__('fields.end_date'))
                        ->native(false)
                        ->displayFormat('d/m/Y H:i'),
                    DateTimePicker::make('subscription.grace_days_ended_at')
                        ->label(__('fields.grace_days_ended_at'))
                        ->native(false)
                        ->displayFormat('d/m/Y H:i')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get): bool => filled($get('subscription.plan_id')))
                ->columnSpanFull(),
        ];
    }
}
