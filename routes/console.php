<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('subscriptions:auto-renew-saved-cards --days=3')
    ->dailyAt('02:15')
    ->withoutOverlapping()
    ->onOneServer();
