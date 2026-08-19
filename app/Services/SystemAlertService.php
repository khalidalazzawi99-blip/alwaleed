<?php

namespace App\Services;

use App\Models\Cashbox;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class SystemAlertService
{
    public function ensureFor(User $user): void
    {
        if ($user->company_id && $user->company) {
            $this->subscriptionAlert($user);
            $this->cashboxAlert($user);
        }

        if ($user->role === 'super_admin') {
            $this->backupAlert($user);
        }
    }

    private function subscriptionAlert(User $user): void
    {
        $end = $user->company->subscription_end;
        if (!$end) return;

        $days = now()->startOfDay()->diffInDays(Carbon::parse($end)->startOfDay(), false);
        if ($days < 0 || $days > config('notifications.subscription_warning_days')) return;

        $this->daily($user, 'subscription', __('messages.subscription_alert_title'),
            __('messages.subscription_alert_message', ['days' => $days]), '/dashboard');
    }

    private function cashboxAlert(User $user): void
    {
        $balance = Cashbox::where('company_id', $user->company_id)->value('balance');
        if ($balance === null || $balance >= config('notifications.low_balance_threshold')) return;

        $this->daily($user, 'low_balance', __('messages.balance_alert_title'),
            __('messages.balance_alert_message', ['balance' => number_format($balance, 2)]),
            in_array($user->role, ['admin', 'accountant']) ? '/cashbox' : '/dashboard');
    }

    private function backupAlert(User $user): void
    {
        $directory = storage_path('app/backups');
        $latest = File::isDirectory($directory)
            ? collect(File::files($directory))->max(fn ($file) => $file->getMTime())
            : null;

        if ($latest && Carbon::createFromTimestamp($latest)->greaterThan(now()->subDays(config('notifications.backup_warning_days')))) return;

        $this->daily($user, 'backup_overdue', __('messages.backup_alert_title'),
            __('messages.backup_alert_message', ['days' => config('notifications.backup_warning_days')]), '/admin/dashboard');
    }

    private function daily(User $user, string $kind, string $title, string $message, string $link): void
    {
        SystemNotification::firstOrCreate(
            ['user_id' => $user->id, 'fingerprint' => $kind.'-'.now()->toDateString()],
            ['company_id' => $user->company_id, 'kind' => $kind, 'title' => $title, 'message' => $message, 'link' => $link],
        );
    }
}
