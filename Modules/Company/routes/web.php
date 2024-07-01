<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['middleware' => ['web']], function () {


    // Company registration routes
    Route::get('/company-register', [Modules\Company\Http\Controllers\Site\CompanyController::class, 'show_registration_form'])->name('site.company.register.form');
    Route::post('/company-register', [Modules\Company\Http\Controllers\Site\CompanyController::class, 'register'])->name('site.company.register');

    Route::post('/company-logout', [Modules\Company\Http\Controllers\Site\CompanyController::class, 'logout'])->name('site.company.logout');

    Route::get('/company-login', [Modules\Company\Http\Controllers\Site\CompanyController::class, 'show_login_form'])->name('login');

    Route::resource('company', Modules\Company\Http\Controllers\Site\CompanyController::class)->names('company');

    Route::middleware(['auth:company'])->group(function () {
        Route::get('/plans', [Modules\Company\Http\Controllers\Site\PlanController::class, 'index'])->name('site.company.plans_subscription_page');
    });
});
