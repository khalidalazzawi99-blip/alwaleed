<?php

return [
    'low_balance_thresholds' => [
        'IQD' => (float) env('NOTIFICATION_LOW_BALANCE_IQD', 100000),
        'USD' => (float) env('NOTIFICATION_LOW_BALANCE_USD', 5000),
    ],
    'subscription_warning_days' => (int) env('NOTIFICATION_SUBSCRIPTION_DAYS', 7),
    'backup_warning_days' => (int) env('NOTIFICATION_BACKUP_DAYS', 7),
    'poll_seconds' => (int) env('NOTIFICATION_POLL_SECONDS', 20),
];
