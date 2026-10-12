<?php

namespace App\Filament\Clusters\Pages\Settings;

use App\Filament\Clusters\Settings;
use App\Services\HyperPay\HyperPayCheckoutService;
use App\Services\HyperPay\HyperPayConfig;
use App\Services\HyperPay\HyperPayGateway;
use App\Services\HyperPay\HyperPaySettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class HyperPayConfiguration extends BaseSettings
{
    use HasPageShield;

    protected static ?string $cluster = Settings::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 3;

    public function getTitle(): string
    {
        return __('main.hyperpay_settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.hyperpay_settings');
    }

    public function schema(): array|Closure
    {
        return [
            Section::make(__('fields.hyperpay'))
                ->description(__('fields.hyperpay_settings_help'))
                ->columns(2)
                ->schema([
                    Toggle::make('hyperpay.enabled')
                        ->label(__('fields.hyperpay_enabled'))
                        ->helperText(__('fields.hyperpay_enabled_help'))
                        ->inline(false)
                        ->onColor('success'),
                    Select::make('hyperpay.mode')
                        ->label(__('fields.hyperpay_mode'))
                        ->options([
                            'test' => __('fields.hyperpay_mode_test'),
                            'live' => __('fields.hyperpay_mode_live'),
                        ])
                        ->required(),
                    TextInput::make('hyperpay.test_base_url')
                        ->label(__('fields.hyperpay_test_base_url'))
                        ->url()
                        ->maxLength(255),
                    TextInput::make('hyperpay.live_base_url')
                        ->label(__('fields.hyperpay_live_base_url'))
                        ->url()
                        ->maxLength(255),
                    TextInput::make('hyperpay.access_token')
                        ->label(__('fields.hyperpay_access_token'))
                        ->password()
                        ->revealable()
                        ->helperText(__('fields.hyperpay_access_token_help'))
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->maxLength(2000)
                        ->columnSpanFull(),
                    TextInput::make('hyperpay.entity_id')
                        ->label(__('fields.hyperpay_entity_id'))
                        ->helperText(__('fields.hyperpay_entity_id_help'))
                        ->maxLength(64),
                    TextInput::make('hyperpay.currency')
                        ->label(__('fields.hyperpay_currency'))
                        ->maxLength(3)
                        ->default('SAR'),
                    Select::make('hyperpay.payment_type')
                        ->label(__('fields.hyperpay_payment_type'))
                        ->options([
                            'DB' => 'DB — Debit / capture',
                            'PA' => 'PA — Pre-authorization',
                        ]),
                    TextInput::make('hyperpay.brands')
                        ->label(__('fields.hyperpay_brands'))
                        ->helperText(__('fields.hyperpay_brands_help'))
                        ->maxLength(80)
                        ->columnSpanFull(),
                    Toggle::make('hyperpay.round_test_amounts')
                        ->label(__('fields.hyperpay_round_test_amounts'))
                        ->helperText(__('fields.hyperpay_round_test_amounts_help'))
                        ->inline(false)
                        ->onColor('success'),
                    Select::make('hyperpay.widget_locale')
                        ->label(__('fields.hyperpay_widget_locale'))
                        ->options([
                            'ar' => 'العربية',
                            'en' => 'English',
                        ]),
                    TextInput::make('hyperpay.merchant_portal_url')
                        ->label(__('fields.hyperpay_merchant_portal'))
                        ->url()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('hyperpay.extra_widget_js')
                        ->label(__('fields.hyperpay_extra_widget_js'))
                        ->helperText(__('fields.hyperpay_extra_widget_js_help'))
                        ->rows(6)
                        ->columnSpanFull(),
                    Actions::make([
                        FormAction::make('fill_hyperpay_test_defaults')
                            ->label(__('fields.hyperpay_fill_test_defaults'))
                            ->icon('heroicon-o-beaker')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading(__('fields.hyperpay_fill_test_defaults'))
                            ->modalDescription(__('fields.hyperpay_fill_test_defaults_help'))
                            ->action(fn () => $this->fillHyperPayTestDefaults()),
                        FormAction::make('verify_hyperpay')
                            ->label(__('fields.hyperpay_verify'))
                            ->icon('heroicon-o-check-badge')
                            ->color('success')
                            ->action(fn () => $this->verifyHyperPayConnection()),
                    ])->columnSpanFull(),
                ]),
        ];
    }

    public function fillHyperPayTestDefaults(): void
    {
        HyperPaySettings::applyTestDefaults();

        try {
            $checkoutId = $this->createVerifyCheckout();
            Notification::make()
                ->title(__('fields.hyperpay_fill_test_defaults_success'))
                ->body(__('fields.hyperpay_credentials_ok').($checkoutId ? ': '.$checkoutId : ''))
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title(__('fields.hyperpay_fill_test_defaults_saved'))
                ->body($exception->getMessage())
                ->warning()
                ->send();
        }

        $this->redirect(static::getUrl());
    }

    public function verifyHyperPayConnection(): void
    {
        try {
            $checkoutId = $this->createVerifyCheckout();
            Notification::make()
                ->title(__('fields.hyperpay_credentials_ok'))
                ->body($checkoutId)
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title(__('fields.hyperpay_credentials_failed'))
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function createVerifyCheckout(): string
    {
        $defaults = HyperPaySettings::testDefaults();
        $config = HyperPayConfig::instance([
            'enabled' => true,
            'mode' => 'test',
            'test_base_url' => $defaults['test_base_url'] ?? 'https://eu-test.oppwa.com',
            'access_token' => HyperPayConfig::instance()->accessToken() ?: ($defaults['access_token'] ?? ''),
            'entity_id' => HyperPayConfig::instance()->entityId() ?: ($defaults['entity_id'] ?? ''),
            'currency' => 'SAR',
            'payment_type' => 'DB',
            'round_test_amounts' => true,
        ]);

        $gateway = HyperPayGateway::make($config);
        $service = new HyperPayCheckoutService($config, $gateway);
        $checkout = $gateway->createCheckout($service->checkoutPayload(
            $config->formatAmount(1),
            'CL-VERIFY-'.now()->format('YmdHis'),
            HyperPayCheckoutService::placeholderBilling(),
        ));

        return (string) ($checkout['id'] ?? '');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('general.save'))
                ->submit('data')
                ->keyBindings(['mod+s']),
        ];
    }
}
