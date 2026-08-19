<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'user_name',
        'action',
        'event',
        'auditable_type',
        'auditable_id',
        'details',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Audit logs are immutable.'));
        static::deleting(fn () => throw new \LogicException('Audit logs cannot be deleted manually.'));
    }
}
