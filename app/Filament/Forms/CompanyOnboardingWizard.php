<?php

namespace App\Filament\Forms;

use App\Models\Plan;
use App\Models\User;
use App\Services\CompanyAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;

class CompanyOnboardingWizard
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    static::companyStep(),
                    static::userStep(),
                    static::subscriptionStep(),
                ])
                    ->columnSpanFull()
                    ->skippable(false)
                    ->persistStepInQueryString()
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            size="lg"
                            wire:loading.attr="disabled"
                            wire:target="create"
                        >
                            <span wire:loading.remove wire:target="create">
                                @lang('main.wizard.complete_setup')
                            </span>
                            <span wire:loading wire:target="create">
                                @lang('main.wizard.saving')
                            </span>
                        </x-filament::button>
                    BLADE))),
            ]);
    }

    protected static function companyStep(): Wizard\Step
    {
        return Wizard\Step::make('company')
            ->label(__('main.wizard.company_information'))
            ->icon('heroicon-o-building-office-2')
            ->columns(2)
            ->schema(CompanyAction::getCompanyWizardSchema());
    }

    protected static function userStep(): Wizard\Step
    {
        return Wizard\Step::make('user')
            ->label(__('main.wizard.user_information'))
            ->icon('heroicon-o-user-circle')
            ->columns(2)
            ->schema([
                TextInput::make('user.name')
                    ->label(__('fields.name'))
                    ->minLength(2)
                    ->required()
                    ->maxLength(255),
                TextInput::make('user.email')
                    ->label(__('fields.email'))
                    ->email()
                    ->unique(User::class, 'email')
                    ->required()
                    ->maxLength(255),
                TextInput::make('user.phone_number')
                    ->label(__('fields.phone_number'))
                    ->tel()
                    ->maxLength(25),
                Select::make('user.roles')
                    ->label(__('filament-shield::filament-shield.resource.label.roles'))
                    ->options(Role::query()->pluck('name', 'id'))
                    ->multiple()
                    ->preload()
                    ->searchable(),
                TextInput::make('user.password')
                    ->label(__('fields.password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Hidden::make('user.is_company')->default(true),
                Hidden::make('user.email_verified_at')->default(now()),
            ]);
    }

    protected static function subscriptionStep(): Wizard\Step
    {
        return Wizard\Step::make('subscription')
            ->label(__('main.wizard.subscription_information'))
            ->icon('heroicon-o-credit-card')
            ->columns(2)
            ->schema([
                Select::make('subscription.plan_id')
                    ->label(__('fields.plan'))
                    ->options(
                        Plan::query()
                            ->get()
                            ->mapWithKeys(fn (Plan $plan) => [
                                $plan->id => (app()->getLocale() === 'ar' ? $plan->name_ar : $plan->name)
                                    ." - {$plan->periodicity_type}",
                            ])
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                DateTimePicker::make('subscription.started_at')
                    ->label(__('fields.start_date'))
                    ->required()
                    ->default(now()),
                DateTimePicker::make('subscription.expired_at')
                    ->label(__('fields.end_date')),
                DateTimePicker::make('subscription.grace_days_ended_at')
                    ->label(__('fields.grace_days_ended_at')),
            ]);
    }
}
