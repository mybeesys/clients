<?php

namespace App\Filament\Forms;

use App\Models\User;
use App\Services\CompanyAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
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
                    SubscriptionWizardStep::make(),
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
                Radio::make('user.link_mode')
                    ->label(__('main.wizard.user_link_mode'))
                    ->options([
                        'new' => __('main.wizard.user_link_new'),
                        'existing' => __('main.wizard.user_link_existing'),
                    ])
                    ->default('new')
                    ->live()
                    ->columnSpanFull(),
                Select::make('user.existing_user_id')
                    ->label(__('fields.user'))
                    ->options(fn () => User::query()->orderBy('email')->pluck('email', 'id'))
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get) => $get('user.link_mode') === 'existing')
                    ->required(fn (callable $get) => $get('user.link_mode') === 'existing')
                    ->columnSpanFull(),
                TextInput::make('user.name')
                    ->label(__('fields.name'))
                    ->minLength(2)
                    ->required(fn (callable $get) => $get('user.link_mode') !== 'existing')
                    ->visible(fn (callable $get) => $get('user.link_mode') !== 'existing')
                    ->maxLength(255)
                    ->unique(User::class, 'name'),
                TextInput::make('user.email')
                    ->label(__('fields.email'))
                    ->email()
                    ->unique(User::class, 'email')
                    ->required(fn (callable $get) => $get('user.link_mode') !== 'existing')
                    ->visible(fn (callable $get) => $get('user.link_mode') !== 'existing')
                    ->maxLength(255),
                TextInput::make('user.phone_number')
                    ->label(__('fields.phone_number'))
                    ->tel()
                    ->visible(fn (callable $get) => $get('user.link_mode') !== 'existing')
                    ->maxLength(25),
                Select::make('user.roles')
                    ->label(__('filament-shield::filament-shield.resource.label.roles'))
                    ->options(Role::query()->pluck('name', 'id'))
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->visible(fn (callable $get) => $get('user.link_mode') !== 'existing'),
                TextInput::make('user.password')
                    ->label(__('fields.password'))
                    ->password()
                    ->revealable()
                    ->required(fn (callable $get) => $get('user.link_mode') !== 'existing')
                    ->visible(fn (callable $get) => $get('user.link_mode') !== 'existing')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Hidden::make('user.is_company')->default(true),
                Hidden::make('user.email_verified_at')->default(now()),
            ]);
    }

}
