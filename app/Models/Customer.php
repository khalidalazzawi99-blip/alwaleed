<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            $customer->integration_id ??= 'C-'.Str::ulid();
        });

        static::updating(function (Customer $customer): void {
            if ($customer->isDirty('integration_id') && $customer->getOriginal('integration_id')) {
                $customer->integration_id = $customer->getOriginal('integration_id');
            }
        });
    }

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
    public function externalInvoices() { return $this->hasMany(ExternalInvoice::class); }
    public function externalLinks() { return $this->hasMany(CustomerExternalLink::class); }
}
