<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cashbox extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Cashbox $cashbox): void {
            $cashbox->integration_id ??= 'B-'.Str::ulid();
        });

        static::updating(function (Cashbox $cashbox): void {
            if ($cashbox->isDirty('integration_id') && $cashbox->getOriginal('integration_id')) {
                $cashbox->integration_id = $cashbox->getOriginal('integration_id');
            }
        });
    }

    protected $attributes = ['account_type' => 'cash'];

    protected $fillable = [
        'company_id',
        'name',
        'account_type',
        'bank_name',
        'account_number',
        'balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['balance' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class)->withTimestamps();
    }
}
