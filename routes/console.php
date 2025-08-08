<?php

use App\Jobs\FetchVoicesFromFreetts;
use App\Jobs\SyncVoiceStatusesFromFreetts;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('limits:reset')->dailyAt('00:00');
Schedule::job(new FetchVoicesFromFreetts)->dailyAt('00:00');
Schedule::job(new SyncVoiceStatusesFromFreetts)->dailyAt('00:00');
