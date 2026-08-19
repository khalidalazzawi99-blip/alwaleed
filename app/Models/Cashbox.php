<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashbox extends Model
{
    protected $fillable = [
        'company_id',
        'balance',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}