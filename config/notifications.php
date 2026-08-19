<?php

return [
    'low_balance_threshold' => (float) env('NOTIFICATION_LOW_BALANCE', 100000),
    'subscription_warning_days' => (int) env('NOTIFICATION_SUBSCRIPTION_DAYS', 7),
    'backup_warning_days' => (int) env('NOTIFICATION_BACKUP_DAYS', 7),
    'poll_seconds' => (int) env('NOTIFICATION_POLL_SECONDS', 20),
];
