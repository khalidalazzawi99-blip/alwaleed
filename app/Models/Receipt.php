<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
