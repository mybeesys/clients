<?php

namespace App;
use App\Filament\Pages\Settings\EmailConfiguration;
use App\Filament\Pages\Settings\GeneralSettings;
use App\Filament\Pages\Settings\NotificationConfiguration;
use App\Filament\Pages\Settings\PaymentConfiguration;
use App\Filament\Pages\Settings\SmsConfiguration;
use App\Filament\Pages\Settings\WebsiteSettings;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
trait SideBar
{
    public static function getSidebarItems(): FilamentPageSidebar
    {
        return FilamentPageSidebar::make()
            ->sidebarNavigation()
            ->setTitle(__('main.system_settings'))
            ->setDescription('general, website, sms, payments, email')
            ->setNavigationItems([
                PageNavigationItem::make('General Settings')
                    ->label(__('main.general_settings'))
                    ->url(GeneralSettings::getUrl())
                    ->icon('heroicon-o-cog-6-tooth')
                    ->isActiveWhen(function () {
                        return request()->routeIs(GeneralSettings::getRouteName());
                    })
                    ->visible(true),
                PageNavigationItem::make('Web Settings')
                    ->label(__('main.web_settings'))
                    ->url(WebsiteSettings::getUrl())
                    ->icon('heroicon-o-globe-alt')
                    ->isActiveWhen(function () {
                        return request()->routeIs(WebsiteSettings::getRouteName());
                    })
                    ->visible(true),
                PageNavigationItem::make('Notification Settings')
                    ->label(__('main.notifications_settings'))
                    ->url(NotificationConfiguration::getUrl())
                    ->icon('heroicon-o-globe-alt')
                    ->isActiveWhen(function () {
                        return request()->routeIs(NotificationConfiguration::getRouteName());
                    })
                    ->visible(true),
                PageNavigationItem::make('SMS Configuration')
                    ->label(__('main.SMS_settings'))
                    ->url(SmsConfiguration::getUrl())
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->isActiveWhen(function () {
                        return request()->routeIs(SmsConfiguration::getRouteName());
                    })
                    ->visible(true),
                PageNavigationItem::make('Payment Configuration')
                    ->label(__('main.payment_settings'))
                    ->url(PaymentConfiguration::getUrl())
                    ->icon('heroicon-o-currency-dollar')
                    ->isActiveWhen(function () {
                        return request()->routeIs(PaymentConfiguration::getRouteName());
                    })
                    ->visible(true),
                PageNavigationItem::make('Email Configuration')
                    ->label(__('main.email_settings'))
                    ->url(EmailConfiguration::getUrl())
                    ->icon('heroicon-o-envelope')
                    ->isActiveWhen(function () {
                        return request()->routeIs(EmailConfiguration::getRouteName());
                    })
                    ->visible(true),
            ]);
    }
}
