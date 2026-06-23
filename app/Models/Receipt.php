<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = [
    'receipt_no',
    'receipt_date',
    'customer_id',
    'amount',
    'notes'
];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}