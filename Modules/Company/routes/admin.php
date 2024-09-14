<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::post('/plan/switch', [Modules\Company\Http\Controllers\Admin\PlanController::class, 'switch_plan'])->name('plan.switch');
});
