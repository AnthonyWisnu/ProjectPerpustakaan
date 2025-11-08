<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Tasks
Schedule::command('loans:send-reminders')
    ->daily()
    ->at('08:00')
    ->description('Send due date reminders for loans due tomorrow');

Schedule::command('activity:clean --days=90')
    ->monthly()
    ->description('Clean activity logs older than 90 days');

// Auto-cancel expired reservations every hour
Schedule::call(function () {
    \App\Jobs\AutoCancelExpiredReservations::dispatch();
})->hourly()
  ->description('Auto-cancel expired reservations');
