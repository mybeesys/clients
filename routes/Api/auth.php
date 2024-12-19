<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete('/company-logout', [AuthController::class, 'destroy'])->name('company-logout');
    Route::get('verify-token', [AuthController::class, 'verifyToken'])->name('verify-token');
});

Route::post('company-login', [AuthController::class, 'store'])->name('company-login');
