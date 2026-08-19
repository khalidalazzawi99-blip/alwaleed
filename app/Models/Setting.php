<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_id',
        'company_name',
        'company_logo',
        'phone',
        'email',
        'address',
        'currency',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}