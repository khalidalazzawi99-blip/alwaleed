<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected static function booted(): void
    {
        static::created(function (Customer $customer): void {
            if (!$customer->external_customer_id) {
                $candidate = 'C-'.$customer->id;
                if (static::where('company_id', $customer->company_id)->where('external_customer_id', $candidate)->exists()) {
                    $candidate .= '-'.$customer->company_id;
                }
                $customer->forceFill(['external_customer_id' => $candidate])->saveQuietly();
            }
        });
    }

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
