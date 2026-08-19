<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $fillable = [
        'company_id', 'direction', 'event_type', 'external_invoice_id',
        'invoice_no', 'status', 'http_status', 'error_message',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
