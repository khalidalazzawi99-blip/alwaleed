<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;

class SipparCompany
{
    public function forUser(?User $user): Company
    {
        $company = $user?->company;
        abort_unless($company?->isSippar() && $company->status === 'active', 403);
        abort_if($company->subscription_end && now()->startOfDay()->gt($company->subscription_end), 403);
        // Reject ambiguous case variants of the canonical company code.
        abort_unless(Company::whereRaw('LOWER(code) = ?', [strtolower(config('daily_accounts.company_code'))])->count() === 1, 403);

        return $company;
    }
}
