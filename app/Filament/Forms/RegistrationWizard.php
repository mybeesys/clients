<?php

namespace App\Filament\Forms;

use App\Models\User;
use App\Services\CompanyAction;
use App\Services\RegistrationEmailVerifier;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
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
        $livewire = $form->getLivewire();
        $email = is_object($livewire) ? (string) data_get($livewire->data ?? [], 'email', '') : '';
        $emailVerified = $email !== '' && app(RegistrationEmailVerifier::class)->isVerified($email);

        return $form
            ->schema([
                Wizard::make([
                    PlanConfiguratorWizardStep::make(
                        required: true,
                        afterValidation: function ($livewire) {
                            if (method_exists($livewire, 'startRegistrationPayment')) {
                                $livewire->startRegistrationPayment();
                            }
                        },
                    ),
                    static::companyStep(),
                    static::accountStep(),
                ])
                    ->columnSpanFull()
                    ->skippable(false)
                    ->persistStepInQueryString()
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <div class="mb-register-submit">
                            <x-filament::button
                                type="submit"
                                size="lg"
                                :disabled="! $emailVerified"
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
                            @unless ($emailVerified)
                                <p class="mb-register-submit__lock">@lang('main.wizard.email_verify_required')</p>
                            @endunless
                        </div>
                        <style>
                            .mb-register-submit { display: flex; flex-direction: column; align-items: flex-start; gap: .45rem; }
                            .mb-register-submit__lock { margin: 0; color: #b88912; font-weight: 700; font-size: .85rem; line-height: 1.6; }
                        </style>
                    BLADE, ['emailVerified' => $emailVerified]))),
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
                            ->unique(User::class, 'email')
                            ->live(onBlur: true)
                            ->rule(function (): \Closure {
                                return function (string $attribute, mixed $value, \Closure $fail): void {
                                    if (! app(RegistrationEmailVerifier::class)->isVerified((string) $value)) {
                                        $fail(__('main.wizard.email_verify_required'));
                                    }
                                };
                            })
                            ->afterStateUpdated(function ($state, $livewire): void {
                                if (isset($livewire->accountEmailVerificationError)) {
                                    $livewire->accountEmailVerificationError = null;
                                }

                                if (method_exists($livewire, 'sendAccountVerificationCode')) {
                                    $livewire->sendAccountVerificationCode(automatic: true);
                                }
                            }),
                        Placeholder::make('email_verification_panel')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->content(function ($livewire): HtmlString {
                                return new HtmlString(view('filament.forms.registration-email-verification', [
                                    'page' => $livewire,
                                ])->render());
                            }),
                        TextInput::make('phone_number')
                            ->label(__('fields.phone_number'))
                            ->tel()
                            ->required()
                            ->prefix('+966')
                            ->placeholder('5XXXXXXXX')
                            ->maxLength(20)
                            ->rule('regex:/^(?:\+?966|0)?5[0-9]{8}$/')
                            ->validationMessages([
                                'regex' => __('main.wizard.saudi_phone_invalid'),
                            ])
                            ->dehydrateStateUsing(function (?string $state): ?string {
                                if (! filled($state)) {
                                    return null;
                                }

                                $digits = preg_replace('/\D+/', '', $state) ?? '';

                                if (str_starts_with($digits, '966')) {
                                    $digits = substr($digits, 3);
                                }

                                if (str_starts_with($digits, '0')) {
                                    $digits = substr($digits, 1);
                                }

                                return '+966'.$digits;
                            })
                            ->columnSpanFull(),
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
                        Hidden::make('referral_code')
                            ->default(fn () => session('referral_code')),
                    ]),
            ]);
    }

    protected static function companyStep(): Wizard\Step
    {
        return Wizard\Step::make('company')
            ->label(__('main.wizard.company_information'))
            ->icon('heroicon-o-building-office-2')
            ->columns(2)
            ->schema(CompanyAction::getRegistrationCompanyWizardSchema('company'));
    }
}
