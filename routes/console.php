<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// These commands reconcile provider state and deliver the durable mail outbox.
Schedule::command('payments:reconcile-checkouts')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('payments:send-notifications')->everyMinute()->withoutOverlapping();
Schedule::command('payments:resume-finalizations')->everyFiveMinutes()->withoutOverlapping();
