<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\ActivityLog;
use App\Models\SystemNotification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('audit:prune', function () {
    $deleted = ActivityLog::query()
        ->where('created_at', '<', now()->subDays(30))
        ->toBase()
        ->delete();

    SystemNotification::query()
        ->where('created_at', '<', now()->subDays(30))
        ->delete();

    $this->info("Deleted {$deleted} audit log(s) older than 30 days.");
})->purpose('Delete immutable audit logs older than 30 days');

Schedule::command('audit:prune')
    ->dailyAt('02:10')
    ->withoutOverlapping();
