<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'code',
        'logo',
        'phone',
        'email',
        'address',
        'subscription_start',
        'subscription_end',
        'status',
        'max_users',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function features()
    {
        return $this->hasMany(CompanyFeature::class);
    }
    public function externalInvoiceIntegrations() { return $this->hasMany(ExternalInvoiceIntegration::class); }
    public function apiTokens() { return $this->hasMany(CompanyApiToken::class); }

    public function hasFeature(string $key): bool
    {
        if (!array_key_exists($key, config('features.modules', []))) {
            return false;
        }

        if ($this->relationLoaded('features')) {
            return (bool) optional($this->features->firstWhere('feature_key', $key))->enabled;
        }

        return $this->features()->where('feature_key', $key)->where('enabled', true)->exists();
    }
}
