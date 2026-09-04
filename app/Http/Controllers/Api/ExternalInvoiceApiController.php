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
            'invoice_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'order_no' => ['required', 'string', 'max:191'],
            'customer_id' => ['nullable', 'required_without:external_customer_id', 'string', 'max:191'],
            'external_customer_id' => ['nullable', 'required_without:customer_id', 'string', 'max:191'],
            'invoice_date' => ['required', 'date'],
            'currency' => ['required', 'string', 'size:3'],
            'amount' => ['required', 'decimal:0,2', 'gt:0'],
            'status' => ['required', 'in:active,cancelled'],
        ]);

        if ($validator->fails()) {
            $this->record($company->id, $request, 'validation_failed', 422, 'Payload validation failed.');
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        if (!empty($data['customer_id']) && !empty($data['external_customer_id']) && $data['customer_id'] !== $data['external_customer_id']) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => ['customer_id' => ['customer_id and external_customer_id must match when both are supplied.']],
            ], 422);
        }
        $publicCustomerId = $data['customer_id'] ?? $data['external_customer_id'];
        $data['currency'] = strtoupper($data['currency']);
        $companyCurrency = strtoupper(Setting::where('company_id', $company->id)->value('currency') ?: 'IQD');
        if ($data['currency'] !== $companyCurrency) {
            return response()->json([
                'message' => 'Currency is not supported for this company.',
                'errors' => ['currency' => ["The supported currency is {$companyCurrency}."]],
            ], 422);
        }
        $customer = Customer::query()
            ->where('company_id', $company->id)
            ->where('integration_id', $publicCustomerId)
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
                        'external_customer_id' => $customer->integration_id,
                        'invoice_no' => $data['invoice_no'],
                        'invoice_name' => $data['invoice_name'] ?? $data['description'] ?? null,
                        'order_no' => $data['order_no'],
                        'invoice_date' => $data['invoice_date'],
                        'amount' => $data['amount'],
                        'currency' => $data['currency'],
                        'status' => $data['status'],
                    ]
                );
            });

            ExternalInvoiceIntegration::where('company_id', $company->id)
                ->where('provider', 'default')->update(['last_sync_at' => now()]);
            $this->record($company->id, $request, $invoice->wasRecentlyCreated ? 'invoice_received' : 'invoice_replayed', $invoice->wasRecentlyCreated ? 201 : 200);

            return response()->json([
                'message' => $invoice->wasRecentlyCreated ? 'Invoice received.' : 'Invoice replayed and safely updated.',
                'data' => $this->invoiceData($invoice->fresh()),
            ], $invoice->wasRecentlyCreated ? 201 : 200);
        } catch (Throwable $exception) {
            Log::error('External invoice receive failed.', ['company_id' => $company->id, 'exception' => $exception::class]);
            $this->record($company->id, $request, 'database_error', 500, 'Invoice could not be stored.');
            return response()->json(['message' => 'Invoice could not be stored.'], 500);
        }
    }

    public function cancel(Request $request, string $externalInvoiceId)
    {
        $company = $request->attributes->get('company');
        $invoice = ExternalInvoice::where('company_id', $company->id)
            ->where('external_invoice_id', $externalInvoiceId)->firstOrFail();
        $invoice->update(['status' => 'cancelled']);
        $this->record($company->id, new Request([
            'external_invoice_id' => $invoice->external_invoice_id,
            'invoice_no' => $invoice->invoice_no,
        ]), 'invoice_cancelled', 200);

        return response()->json(['message' => 'Invoice cancelled.', 'data' => $invoice->fresh()->only(['external_invoice_id', 'invoice_no', 'status'])]);
    }

    public function index(Request $request)
    {
        $request->validate(['per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $page = ExternalInvoice::query()->where('company_id', $request->attributes->get('company')->id)
            ->latest('invoice_date')->latest('id')->paginate(max(1, min($request->integer('per_page', 100), 100)))
            ->withQueryString();

        return response()->json([
            'data' => $page->getCollection()->map(fn (ExternalInvoice $invoice) => $this->invoiceData($invoice))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function balance(Request $request, string $customerId, CustomerBalanceService $balances)
    {
        $customer = Customer::where('company_id', $request->attributes->get('company')->id)
            ->where('integration_id', $customerId)->firstOrFail();
        $balance = $balances->calculate($customer);
        $currency = strtoupper(Setting::where('company_id', $customer->company_id)->value('currency') ?: 'IQD');

        return response()->json([
            'data' => [
                'customer_id' => $customer->integration_id,
                'name' => $customer->name,
                'total_invoices' => round($balance['totalInvoices'], 2),
                'total_receipts' => round($balance['totalReceipts'], 2),
                'total_payments' => round($balance['totalPayments'], 2),
                'current_balance' => round($balance['currentBalance'], 2),
                'balance' => round($balance['currentBalance'], 2),
                'currency' => $currency,
                'updated_at' => $customer->updated_at?->utc()->toIso8601String(),
            ],
        ]);
    }

    private function invoiceData(ExternalInvoice $invoice): array
    {
        return [
            'external_invoice_id' => $invoice->external_invoice_id,
            'invoice_no' => $invoice->invoice_no,
            'invoice_name' => $invoice->invoice_name,
            'order_no' => $invoice->order_no,
            'customer_id' => $invoice->external_customer_id,
            'invoice_date' => $invoice->invoice_date?->toDateString(),
            'currency' => $invoice->currency,
            'amount' => (float) $invoice->amount,
            'status' => $invoice->status,
            'created_at' => $invoice->created_at?->utc()->toIso8601String(),
            'updated_at' => $invoice->updated_at?->utc()->toIso8601String(),
        ];
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
