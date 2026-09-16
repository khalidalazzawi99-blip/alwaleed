<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Receipt extends Model
{
    protected $touches = ['customer'];

    protected $fillable = [
        'company_id',
        'cashbox_id',
        'receipt_no',
        'receipt_date',
        'customer_id',
        'supplier_id',
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
        return ['receipt_date' => 'date', 'cancelled_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (Receipt $receipt): void {
            $receipt->verification_token ??= Str::random(64);
            $receipt->status ??= 'active';
            $receipt->created_by ??= auth()->id();
        });
        static::updating(function (Receipt $receipt): void {
            if ($receipt->isDirty('receipt_no')) {
                $receipt->receipt_no = $receipt->getOriginal('receipt_no');
            }
            if (auth()->id()) {
                $receipt->updated_by = auth()->id();
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

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
