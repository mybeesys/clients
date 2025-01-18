<?php

use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\LocalizationMiddleware;
use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return to_route('filament.admin.auth.login');
});


Route::get('/login', function () {
    return to_route('filament.admin.auth.login');
})->name('login');

Route::get('/subscribe', function () {
    $plans = Plan::where('active', true)->get();
    $features = Feature::whereHas('feature_plans')->get();
    return view('subscriptions.subscribe', compact('plans', 'features'));
})->middleware(LocalizationMiddleware::class)->middleware('auth')->name('subscribe');

Route::get('/subscribe2', function () {
    $plans = Plan::where('active', true)->get();
    $features = Feature::whereHas('feature_plans')->get();
    return view('subscriptions.subscribe2', compact('plans', 'features'));
})->middleware('auth')->name('subscribe2');

Route::post('/plan/subscribe', [SubscriptionController::class, 'store'])->middleware('auth');

Route::get('/set-locale/{locale}', function ($locale) {
    session()->put('locale', $locale);
    app()->setLocale($locale);
    return redirect()->back();
})->name('set_locale');