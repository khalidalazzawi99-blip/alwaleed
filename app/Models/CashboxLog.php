<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashboxLog extends Model
{
    protected $fillable = [
        'company_id',
        'cashbox_id',
        'type',
        'reference_no',
        'person_name',
        'amount',
        'balance_after',
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
}
