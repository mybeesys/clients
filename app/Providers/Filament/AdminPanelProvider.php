<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Registration;
use App\Http\Middleware\EnsureSubscription;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->registration(Registration::class)
            ->login(Login::class)
            ->brandName('My Bee')
            ->brandLogo(asset('images/brand/mybee-lockup.png'))
            ->darkModeBrandLogo(asset('images/brand/mybee-lockup.png'))
            ->brandLogoHeight('3.25rem')
            ->favicon(asset('images/brand/mybee-mark.png'))
            ->font('Cairo', provider: GoogleFontProvider::class)
            ->colors([
                'primary' => Color::hex('#ebb81e'),
                'warning' => Color::hex('#b88912'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureSubscription::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn (): string => __('main.users_management'))
                    ->icon('heroicon-o-users')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn (): string => __('main.reports_management'))
                    ->icon('heroicon-o-flag')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn (): string => __('main.companies_management'))
                    ->icon('heroicon-o-flag')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn (): string => __('main.subscriptions_management'))
                    ->icon('heroicon-o-inbox-stack')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn (): string => __('main.coupons_management'))
                    ->icon('heroicon-o-receipt-percent')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn (): string => __('main.settings'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 2,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => Blade::render('<div class="mybee-auth-honeycomb" aria-hidden="true"></div><div class="mybee-auth-glow" aria-hidden="true"></div>'),
                scopes: [
                    Registration::class,
                    Login::class,
                ],
            );
    }
}
