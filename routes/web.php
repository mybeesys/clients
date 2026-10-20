<?php

use App\Http\Controllers\HyperPayCheckoutController;
use App\Http\Controllers\Internal\GrantTenantAdminPermissionsController;
use App\Http\Controllers\InviteLandingController;
use App\Http\Controllers\RegistrationThankYouController;
use App\Http\Controllers\SubscribeEmbedErrorController;
use App\Http\Controllers\SubscribeHandoffController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\AllowSubscribeFrame;
use App\Http\Middleware\LocalizationMiddleware;
use App\Livewire\ManageSubscription;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return to_route('filament.admin.auth.login');
});

Route::get(
    '/internal/tenants/{tenant}/grant-admin-permissions',
    GrantTenantAdminPermissionsController::class,
)->name('internal.tenants.grant-admin-permissions');


Route::get('/login', function () {
    return to_route('filament.admin.auth.login');
})->name('login');

Route::get('/register/thank-you', RegistrationThankYouController::class)
    ->name('register.thank-you');

Route::get('/pay/hyperpay/{uid}', [HyperPayCheckoutController::class, 'show'])
    ->middleware(LocalizationMiddleware::class)
    ->name('hyperpay.checkout');

Route::match(['get', 'post'], '/pay/hyperpay/{uid}/result', [HyperPayCheckoutController::class, 'result'])
    ->middleware(LocalizationMiddleware::class)
    ->name('hyperpay.result');

Route::get('/invite/{code}', InviteLandingController::class)
    ->middleware(LocalizationMiddleware::class)
    ->name('referrals.landing');

Route::get('/subscribe/handoff/{token}', SubscribeHandoffController::class)
    ->middleware([LocalizationMiddleware::class, AllowSubscribeFrame::class])
    ->where('token', '[A-Za-z0-9]{64}')
    ->name('subscribe.handoff');

Route::get('/subscribe/embed-error', SubscribeEmbedErrorController::class)
    ->middleware([LocalizationMiddleware::class, AllowSubscribeFrame::class])
    ->name('subscribe.embed-error');

Route::get('/subscribe', ManageSubscription::class)
    ->middleware([LocalizationMiddleware::class, AllowSubscribeFrame::class, 'auth'])
    ->name('subscribe');

Route::get('/subscribe/vault-card', \App\Http\Controllers\VaultCardController::class)
    ->middleware([LocalizationMiddleware::class, AllowSubscribeFrame::class, 'auth'])
    ->name('subscribe.vault-card');

Route::post('/plan/subscribe', [SubscriptionController::class, 'store'])->middleware('auth');
Route::post('/switch-plan', [SubscriptionController::class, 'switchPlan'])->middleware('auth');

Route::get('/set-locale/{locale}', function ($locale) {
    session()->put('locale', $locale);
    app()->setLocale($locale);
    return redirect()->back();
})->name('set_locale');