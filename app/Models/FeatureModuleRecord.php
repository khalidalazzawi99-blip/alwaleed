<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureModuleRecord extends Model
{
    protected $fillable = ['company_id', 'module', 'reference', 'name', 'record_date', 'amount', 'status', 'notes'];

    protected function casts(): array
    {
        return ['record_date' => 'date', 'amount' => 'decimal:2'];
    }
}
