<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\ExternalInvoiceIntegration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ActivateCompanyApiToken extends Command
{
    protected $signature = 'company-api-token:activate
        {company : Company ID, code, or exact name}
        {--name=Sippar Lights : A label for the credential}
        {--expires-at= : Optional expiry date/time}';

    protected $description = 'Create or reactivate a company API token entered securely';

    public function handle(): int
    {
        $identifier = trim((string) $this->argument('company'));
        $company = Company::query()
            ->whereKey(ctype_digit($identifier) ? (int) $identifier : 0)
            ->orWhere('code', $identifier)
            ->orWhere('name', $identifier)
            ->first();

        if (! $company) {
            $this->error('Company not found.');

            return self::FAILURE;
        }

        $plain = trim((string) (env('SIPPAR_API_TOKEN') ?: $this->secret('Paste the API token')));

        if (! preg_match('/^aw_live_[A-Za-z0-9]{48}$/', $plain)) {
            $this->error('Invalid token format; expected aw_live_ followed by 48 letters or numbers.');

            return self::FAILURE;
        }

        $expiresAt = $this->option('expires-at');
        $scopes = ['invoices:read', 'invoices:write', 'balances:read', 'customers:read', 'banks:read'];

        DB::transaction(function () use ($company, $plain, $expiresAt, $scopes): void {
            $hash = hash('sha256', $plain);

            CompanyApiToken::where('company_id', $company->id)
                ->whereNull('revoked_at')
                ->where('token_hash', '!=', $hash)
                ->update(['revoked_at' => now()]);

            CompanyApiToken::updateOrCreate(
                ['token_hash' => $hash],
                [
                    'company_id' => $company->id,
                    'name' => (string) $this->option('name'),
                    'token_prefix' => substr($plain, 0, 14),
                    'scopes' => $scopes,
                    'expires_at' => $expiresAt ?: null,
                    'revoked_at' => null,
                ]
            );

            $company->forceFill(['status' => 'active'])->save();

            ExternalInvoiceIntegration::updateOrCreate(
                ['company_id' => $company->id, 'provider' => 'default'],
                ['enabled' => true]
            );
        });

        $this->info('API token activated for '.$company->name.' with all Sippar scopes.');

        return self::SUCCESS;
    }
}
