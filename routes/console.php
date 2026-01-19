<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Here you may define all of your scheduled tasks. These tasks will run
| automatically based on the schedule you define.
|
*/

// Finance: Generate monthly tuition invoices on the 1st of each month at 00:05
Schedule::command('finance:generate-monthly-tuition')
    ->monthlyOn(1, '00:05')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/monthly-tuition.log'))
    ->emailOutputOnFailure(config('mail.admin_email'));

// Finance: Check and expire scholarships daily at 00:01
Schedule::call(function () {
    \App\Models\Finance\StudentScholarship::query()
        ->where('status', \App\Enums\Finance\ScholarshipStatus::ACTIVE)
        ->whereNotNull('valid_until')
        ->where('valid_until', '<', now()->startOfDay())
        ->update(['status' => \App\Enums\Finance\ScholarshipStatus::EXPIRED]);

    \Illuminate\Support\Facades\Log::info('[SCHOLARSHIP] Expired scholarships updated');
})
    ->daily()
    ->at('00:01')
    ->timezone('Asia/Jakarta')
    ->name('scholarship-expiration-check')
    ->withoutOverlapping();

// Academic: Nightly AKM recalculation at 23:00
// Recalculates IPS/IPK for all students with pending updates
Schedule::command('academic:recalculate-akm --dirty')
    ->dailyAt('23:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/akm-recalculation.log'))
    ->name('akm-nightly-recalculation');

// Finance: Send invoice due date reminders daily at 08:00
// Notifies students about invoices due in 7, 3, 1 days and overdue
Schedule::command('finance:send-due-reminders')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/invoice-reminders.log'))
    ->name('invoice-due-reminders');

