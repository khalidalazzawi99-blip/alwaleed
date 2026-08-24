<?php

namespace App\Http\Controllers;

use App\Models\CompanyApiToken;
use App\Models\Customer;
use App\Models\ExternalInvoice;
use App\Models\ExternalInvoiceIntegration;
use App\Models\IntegrationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExternalInvoiceController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        abort_unless($companyId, 403);
        $integration = ExternalInvoiceIntegration::firstOrCreate(
            ['company_id' => $companyId, 'provider' => 'default'], ['enabled' => false]
        );

        return view('external_invoices.index', [
            'integration' => $integration,
            'invoices' => ExternalInvoice::with('customer')->where('company_id', $companyId)->latest('invoice_date')->paginate(25),
            'customers' => Customer::where('company_id', $companyId)->orderBy('name')->get(),
            'tokens' => CompanyApiToken::where('company_id', $companyId)->latest()->get(),
            'invoiceCount' => ExternalInvoice::where('company_id', $companyId)->count(),
            'lastActivity' => IntegrationLog::where('company_id', $companyId)->latest()->first(),
            'recentErrors' => IntegrationLog::where('company_id', $companyId)->where('http_status', '>=', 400)->latest()->limit(10)->get(),
            'hasActiveToken' => CompanyApiToken::where('company_id', $companyId)
                ->whereNull('revoked_at')->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->exists(),
        ]);
    }

    public function saveIntegration(Request $request)
    {
        $data = $request->validate(['enabled' => ['nullable', 'boolean']]);
        ExternalInvoiceIntegration::updateOrCreate(
            ['company_id' => auth()->user()->company_id, 'provider' => 'default'],
            ['enabled' => $request->boolean('enabled')]
        );
        return back()->with('success', __('messages.external_integration_saved'));
    }

    public function linkCustomer(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $data = $request->validate([
            'external_customer_id' => [
                'required', 'string', 'max:191',
                Rule::unique('customers', 'external_customer_id')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($request->integer('customer_id')),
            ],
            'customer_id' => ['required', 'integer'],
        ]);
        $customer = Customer::where('company_id', $companyId)->findOrFail($data['customer_id']);
        $customer->update(['external_customer_id' => $data['external_customer_id']]);
        return back()->with('success', __('messages.customer_linked'));
    }

    public function createToken(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'expires_at' => ['nullable', 'date', 'after:today']]);
        $plain = 'aw_live_'.Str::random(48);
        DB::transaction(function () use ($data, $plain) {
            $companyId = auth()->user()->company_id;
            CompanyApiToken::where('company_id', $companyId)->whereNull('revoked_at')->update(['revoked_at' => now()]);
            CompanyApiToken::create([
                'company_id' => $companyId, 'name' => $data['name'], 'token_hash' => hash('sha256', $plain),
                'token_prefix' => substr($plain, 0, 14), 'scopes' => ['invoices:read', 'invoices:write', 'balances:read', 'customers:read', 'banks:read'],
                'expires_at' => $data['expires_at'] ?? null,
            ]);
            ExternalInvoiceIntegration::updateOrCreate(
                ['company_id' => $companyId, 'provider' => 'default'], ['enabled' => true]
            );
        });
        return back()->with('api_token_plain', $plain)->with('success', __('messages.api_token_created'));
    }

    public function revokeToken(CompanyApiToken $token)
    {
        abort_unless($token->company_id === auth()->user()->company_id, 403);
        $token->update(['revoked_at' => now()]);
        return back()->with('success', __('messages.api_token_revoked'));
    }
}
