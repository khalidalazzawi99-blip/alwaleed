<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'cashbox_id',
        'payment_no',
        'payment_date',
        'supplier_id',
        'customer_id',
        'amount',
        'notes',
        'status',
        'created_by',
        'updated_by',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $hidden = ['verification_token'];

    protected function casts(): array
    {
        return ['payment_date' => 'date', 'cancelled_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            $payment->verification_token ??= Str::random(64);
            $payment->status ??= 'active';
            $payment->created_by ??= auth()->id();
        });
        static::updating(function (Payment $payment): void {
            if ($payment->isDirty('payment_no')) {
                $payment->payment_no = $payment->getOriginal('payment_no');
            }
            if (auth()->id()) {
                $payment->updated_by = auth()->id();
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getPartyAttribute()
    {
        return $this->customer ?: $this->supplier;
    }

    public function getPartyTypeAttribute(): string
    {
        return $this->customer_id ? 'customer' : 'supplier';
    }
}
