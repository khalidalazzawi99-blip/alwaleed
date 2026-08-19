<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'company_id',
        'external_customer_id',
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
    public function externalInvoices() { return $this->hasMany(ExternalInvoice::class); }
    public function externalLinks() { return $this->hasMany(CustomerExternalLink::class); }
}
