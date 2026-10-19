<?php

namespace App\Providers;

use App\Http\Responses\RegistrationThankYouResponse;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RegistrationResponseContract::class, RegistrationThankYouResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $request = request();
        if ($request->isSecure()
            || str_starts_with(strtolower((string) $request->header('X-Forwarded-Proto', '')), 'https')) {
            URL::forceScheme('https');
        }

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->visible(outsidePanels: true)
                ->locales(['ar','en']);
        });

        Authenticate::redirectUsing(function ($request) {
            $dest = strtolower((string) $request->headers->get('Sec-Fetch-Dest', ''));
            $isEmbed = $request->boolean('embed')
                || session('subscribe.embed')
                || in_array($dest, ['iframe', 'nested'], true);

            if ($isEmbed && $request->is('subscribe', 'subscribe/*')) {
                return route('subscribe.embed-error', ['reason' => 'unauthenticated']);
            }

            return route('login');
        });
    }
}
