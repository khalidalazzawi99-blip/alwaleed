<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashboxLog extends Model
{
    protected $fillable = [
        'type',
        'reference_no',
        'person_name',
        'amount',
        'balance_after',
        'notes'
    ];
}