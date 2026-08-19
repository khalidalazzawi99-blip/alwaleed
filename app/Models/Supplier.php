<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'company_name',
        'address',
        'notes',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function receipts() { return $this->hasMany(Receipt::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}
