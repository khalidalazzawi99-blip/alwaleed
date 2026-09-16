<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartyDebtTransaction extends Model
{
    protected $fillable = [
        'company_id', 'customer_id', 'supplier_id', 'type',
        'transaction_date', 'amount', 'notes',
    ];

    protected function casts(): array
    {
        return ['transaction_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function customer() { return $this->belongsTo(Customer::class)->withTrashed(); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
}
