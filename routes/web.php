<?php

use App\Models\Plan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return to_route('filament.admin.auth.login');
});

Route::get('/subscribe', function () {
    $plans= Plan::all();
    return view('subscriptions.subscribe', compact('plans'));
})->middleware('auth')->name('subscribe');
