<?php

use App\Models\Plan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('filament.admin.auth.login');
});

Route::get('/subscribe', function () {
    $plans= Plan::all();
    return view('subscriptions.subscribe', compact('plans'));
})->middleware('auth')->name('subscribe');
