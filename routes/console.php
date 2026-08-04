<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bookings:expire')->everyFiveMinutes();
Schedule::command('invoices:generate-monthly')->daily();
Schedule::command('invoices:mark-overdue')->daily();
Schedule::command('notifications:send-due-reminders')->daily();
Schedule::command('notifications:retry-failed')->hourly();
