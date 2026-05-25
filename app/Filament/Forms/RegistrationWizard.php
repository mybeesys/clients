<?php

namespace App\Filament\Forms;

use App\Models\User;
use App\Services\CompanyAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class RegistrationWizard
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    static::accountStep(),
                    static::companyStep(),
                    SubscriptionWizardStep::make(planRequired: false),
                ])
                    ->columnSpanFull()
                    ->skippable(false)
                    ->persistStepInQueryString()
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            size="lg"
                            wire:loading.attr="disabled"
                            wire:target="register"
                        >
                            <span wire:loading.remove wire:target="register">
                                @lang('general.register')
                            </span>
                            <span wire:loading wire:target="register">
                                @lang('main.wizard.saving')
                            </span>
                        </x-filament::button>
                    BLADE))),
            ]);
    }

    protected static function accountStep(): Wizard\Step
    {
        return Wizard\Step::make('account')
            ->label(__('main.wizard.account_information'))
            ->icon('heroicon-o-user-circle')
            ->columns(2)
            ->schema([
                Section::make(__('main.wizard.account_information'))
                    ->description(__('main.wizard.account_information_hint'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('userName')
                            ->label(__('fields.name'))
                            ->minLength(2)
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class, 'name')
                            ->autofocus(),
                        TextInput::make('email')
                            ->label(__('fields.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class, 'email'),
                        TextInput::make('password')
                            ->label(__('fields.password'))
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(255)
                            ->same('passwordConfirmation')
                            ->columnSpanFull(),
                        TextInput::make('passwordConfirmation')
                            ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(255)
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        Hidden::make('is_company')->default(true),
                    ]),
            ]);
    }

    protected static function companyStep(): Wizard\Step
    {
        return Wizard\Step::make('company')
            ->label(__('main.wizard.company_information'))
            ->icon('heroicon-o-building-office-2')
            ->description(__('main.wizard.company_information_hint_register'))
            ->columns(2)
            ->schema(CompanyAction::getCompanyWizardSchema('company'));
    }
}
