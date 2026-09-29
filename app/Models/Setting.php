<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_id',
        'company_name',
        'company_name_ar',
        'company_name_en',
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
