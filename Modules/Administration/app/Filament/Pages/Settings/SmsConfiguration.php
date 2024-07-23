<?php

namespace Modules\Administration\Filament\Pages\Settings;

use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Pages\Page;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;
use Closure;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;

class SmsConfiguration extends BaseSettings
{
    use HasPageSidebar;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false;
    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('General')
                        ->schema([
                            TextInput::make('general.brand_name')
                                ->required(),
                        ]),
                    Tabs\Tab::make('Seo')
                        ->schema([
                            TextInput::make('seo.title')
                                ->required(),
                            TextInput::make('seo.description')
                                ->required(),
                        ]),

                    Tabs\Tab::make('Contact us')
                        ->schema([
                            TextInput::make('contacts.phone')
                                ->required(),
                            TextInput::make('contacts.email')
                                ->required(),
                        ]),

                    Tabs\Tab::make('Social Media')
                        ->schema([
                            TextInput::make('media.facebook')
                                ->required(),
                            TextInput::make('media.instagram')
                                ->required(),
                            TextInput::make('media.linkedin')
                                ->required(),
                            TextInput::make('media.threads')
                                ->required(),
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
                    ->icon('heroicon-o-cog-6-tooth')
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
                    ->icon('heroicon-o-truck')
                    ->isActiveWhen(function () {
                        return request()->routeIs(EmailConfiguration::getRouteName());
                    })
                    ->visible(true),
            ]);
    }
}
