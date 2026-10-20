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
        'transaction_date',
    ];

    protected $casts = ['transaction_date' => 'date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class)->withTrashed();
    }
}
