<?php

namespace Modules\Administration\Filament\Pages\Settings;

use Closure;

use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;

class GeneralSettings extends BaseSettings
{
    use HasPageSidebar;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = "Settings";

    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('General')
                        ->schema([
                            TextInput::make('general.website_name'),
                            TextInput::make('general.website_description'),

                        ]),


                    Tabs\Tab::make('Contact us')
                        ->schema([
                            TextInput::make('contacts.phone'),
                            TextInput::make('contacts.email'),
                        ]),

                    Tabs\Tab::make('Social Media')
                        ->schema([
                            TextInput::make('media.facebook'),
                            TextInput::make('media.instagram'),
                            TextInput::make('media.linkedin'),
                            TextInput::make('media.threads'),
                        ]),
                ]),
        ];
    }

    public static function sidebar(): FilamentPageSidebar
    {
        return FilamentPageSidebar::make()
            ->sidebarNavigation()
            ->setTitle('Application Settings')
            ->setDescription('general, website, sms, payments, email')
            ->setNavigationItems([
                PageNavigationItem::make('General Settings')
                    ->translateLabel()
                    ->url(GeneralSettings::getUrl())
                    ->icon('heroicon-o-cog-6-tooth')
                    ->isActiveWhen(function () {
                        return request()->routeIs(GeneralSettings::getRouteName());
                    })
                    ->visible(true),

                PageNavigationItem::make('Web Settings')
                    ->translateLabel()
                    ->url(WebsiteSettings::getUrl())
                    ->icon('heroicon-o-globe-alt')
                    ->isActiveWhen(function () {
                        return request()->routeIs(WebsiteSettings::getRouteName());
                    })
                    ->visible(true),
                PageNavigationItem::make('SMS Configuration')
                    ->translateLabel()
                    ->url(SmsConfiguration::getUrl())
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->isActiveWhen(function () {
                        return request()->routeIs(SmsConfiguration::getRouteName());
                    })
                    ->visible(true),

                PageNavigationItem::make('Payment Configuration')
                    ->translateLabel()
                    ->url(PaymentConfiguration::getUrl())
                    ->icon('heroicon-o-currency-dollar')
                    ->isActiveWhen(function () {
                        return request()->routeIs(PaymentConfiguration::getRouteName());
                    })
                    ->visible(true),
                PageNavigationItem::make('Email Configuration')
                    ->translateLabel()
                    ->url(EmailConfiguration::getUrl())
                    ->icon('heroicon-o-envelope')
                    ->isActiveWhen(function () {
                        return request()->routeIs(EmailConfiguration::getRouteName());
                    })
                    ->visible(true),
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Settings';
    }
    public function getTitle(): string
    {
        return 'Settings';
    }
}
