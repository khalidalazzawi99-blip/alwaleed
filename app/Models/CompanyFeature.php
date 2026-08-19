<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyFeature extends Model
{
    protected $fillable = ['company_id', 'feature_key', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
