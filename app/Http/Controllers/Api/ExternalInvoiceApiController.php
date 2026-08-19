<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ExternalInvoice;
use App\Models\ExternalInvoiceIntegration;
use App\Models\IntegrationLog;
use App\Models\Setting;
use App\Services\CustomerBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ExternalInvoiceApiController extends Controller
{
    public function store(Request $request)
    {
        $company = $request->attributes->get('company');
        $validator = Validator::make($request->all(), [
            'company_id' => ['prohibited'],
            'external_invoice_id' => ['required', 'string', 'max:191'],
            'invoice_no' => ['required', 'string', 'max:191'],
            'external_customer_id' => ['required', 'string', 'max:191'],
            'invoice_date' => ['required', 'date'],
            'amount' => ['required', 'decimal:0,2', 'gt:0'],
        ]);

        if ($validator->fails()) {
            $this->record($company->id, $request, 'validation_failed', 422, 'Payload validation failed.');
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $customer = Customer::query()
            ->where('company_id', $company->id)
            ->where('external_customer_id', $data['external_customer_id'])
            ->first();

        if (!$customer) {
            $this->record($company->id, $request, 'customer_not_found', 404, 'Matching customer was not found.');
            return response()->json(['message' => 'Matching customer not found.'], 404);
        }

        try {
            $invoice = DB::transaction(function () use ($company, $customer, $data) {
                return ExternalInvoice::updateOrCreate(
                    ['company_id' => $company->id, 'external_invoice_id' => $data['external_invoice_id']],
                    [
                        'customer_id' => $customer->id,
                        'external_customer_id' => $data['external_customer_id'],
                        'invoice_no' => $data['invoice_no'],
                        'invoice_date' => $data['invoice_date'],
                        'amount' => $data['amount'],
                        'currency' => Setting::where('company_id', $company->id)->value('currency') ?: 'IQD',
                    ]
                );
            });

            ExternalInvoiceIntegration::where('company_id', $company->id)
                ->where('provider', 'default')->update(['last_sync_at' => now()]);
            $this->record($company->id, $request, $invoice->wasRecentlyCreated ? 'invoice_received' : 'invoice_replayed', $invoice->wasRecentlyCreated ? 201 : 200);

            return response()->json([
                'message' => $invoice->wasRecentlyCreated ? 'Invoice received.' : 'Invoice replayed and safely updated.',
                'data' => $invoice->fresh()->only(['id', 'external_invoice_id', 'invoice_no', 'invoice_date', 'amount', 'customer_id']),
            ], $invoice->wasRecentlyCreated ? 201 : 200);
        } catch (Throwable $exception) {
            Log::error('External invoice receive failed.', ['company_id' => $company->id, 'exception' => $exception::class]);
            $this->record($company->id, $request, 'database_error', 500, 'Invoice could not be stored.');
            return response()->json(['message' => 'Invoice could not be stored.'], 500);
        }
    }

    public function index(Request $request)
    {
        return ExternalInvoice::query()->where('company_id', $request->attributes->get('company')->id)
            ->latest('invoice_date')->paginate(min((int) $request->integer('per_page', 50), 100));
    }

    public function balance(Request $request, string $externalCustomerId, CustomerBalanceService $balances)
    {
        $customer = Customer::where('company_id', $request->attributes->get('company')->id)
            ->where('external_customer_id', $externalCustomerId)->firstOrFail();
        return response()->json(['customer' => $customer->only(['id', 'name', 'external_customer_id']), ...$balances->calculate($customer)]);
    }

    private function record(int $companyId, Request $request, string $status, int $httpStatus, ?string $error = null): void
    {
        try {
            IntegrationLog::create([
                'company_id' => $companyId, 'direction' => 'inbound', 'event_type' => 'external_invoice',
                'external_invoice_id' => mb_substr((string) $request->input('external_invoice_id'), 0, 191),
                'invoice_no' => mb_substr((string) $request->input('invoice_no'), 0, 191),
                'status' => $status, 'http_status' => $httpStatus, 'error_message' => $error,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Integration event could not be recorded.', ['company_id' => $companyId, 'status' => $status]);
        }
    }
}
