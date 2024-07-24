<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {

        Route::get('/', function () {
            // dd(DB::connection()->getDatabaseName());
            return view('welcome');
        });
    });
}
