<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_no',
        'payment_date',
        'supplier_id',
        'amount',
        'notes'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}