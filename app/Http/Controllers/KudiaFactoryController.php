<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ExternalInvoice;
use App\Models\Setting;
use App\Services\DocumentExportService;
use App\Services\KudiaInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KudiaFactoryController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId();
        $query = $this->invoices()->with('customer');
        if ($request->filled('q')) {
            $search = mb_substr((string) $request->query('q'), 0, 100);
            $query->where(fn ($q) => $q->where('invoice_no', 'like', '%'.$search.'%')
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', '%'.$search.'%')));
        }

        return view('kudia.index', [
            'invoices' => $query->orderByDesc('invoice_date')->orderByDesc('id')->paginate(20)->withQueryString(),
            'invoiceTotal' => $this->invoices()->where('status', '!=', 'cancelled')->sum('amount'),
            'invoiceCount' => $this->invoices()->where('status', '!=', 'cancelled')->count(),
            'customerCount' => Customer::where('company_id', $companyId)->forAccountGroup(Customer::GROUP_KUDIA)->count(),
            'currency' => $this->currency(),
            'legacyInvoiceCount' => $this->invoices()->whereHas('customer', fn ($query) => $query->forAccountGroup(Customer::GROUP_GENERAL))->count(),
        ]);
    }

    public function customers(Request $request, CustomerController $customers)
    {
        $this->companyId();

        return view('kudia.customers', $customers->index($request)->getData());
    }

    public function storeCustomer(Request $request, CustomerController $customers)
    {
        $this->companyId();
        $customers->store($request);

        return redirect()->route('kudia.customers.index')->with('success', 'تمت إضافة الزبون.');
    }

    public function create()
    {
        return $this->form();
    }

    public function store(Request $request, KudiaInvoiceService $service)
    {
        $data = $this->validateInvoice($request);
        $customer = Customer::where('company_id', $this->companyId())->forAccountGroup(Customer::GROUP_KUDIA)->findOrFail($data['customer_id']);
        $invoice = $service->save($customer, $data);

        return redirect()->route('kudia.invoices.show', $invoice)->with('success', 'تم حفظ الفاتورة وإضافتها لكشف حساب الزبون.');
    }

    public function show(int $invoice)
    {
        return view('kudia.show', ['invoice' => $this->findInvoice($invoice)]);
    }

    public function edit(int $invoice)
    {
        $record = $this->findInvoice($invoice);
        abort_if($record->status === 'cancelled', 422, 'لا يمكن تعديل فاتورة ملغاة.');

        return $this->form($record);
    }

    public function update(Request $request, int $invoice, KudiaInvoiceService $service)
    {
        $data = $this->validateInvoice($request);
        $customer = Customer::where('company_id', $this->companyId())->forAccountGroup(Customer::GROUP_KUDIA)->findOrFail($data['customer_id']);
        DB::transaction(function () use ($invoice, $customer, $data, $service) {
            $record = $this->invoices()->whereKey($invoice)->lockForUpdate()->firstOrFail();
            abort_if($record->status === 'cancelled', 422, 'لا يمكن تعديل فاتورة ملغاة.');
            $service->save($customer, $data, $record);
        });

        return redirect()->route('kudia.invoices.show', $invoice)->with('success', 'تم تعديل الفاتورة وتحديث كشف الحساب.');
    }

    public function destroy(Request $request, int $invoice)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        DB::transaction(function () use ($invoice, $data) {
            $record = $this->invoices()->whereKey($invoice)->lockForUpdate()->firstOrFail();
            if ($record->status !== 'cancelled') {
                $payload = $record->payload;
                $payload['cancellation'] = ['reason' => $data['reason'], 'user_id' => auth()->id(), 'at' => now()->toIso8601String()];
                $record->update(['status' => 'cancelled', 'payload' => $payload]);
            }
        });

        return redirect()->route('kudia.invoices.show', $invoice)->with('success', 'تم إلغاء الفاتورة وإعادة احتساب رصيد الزبون.');
    }

    public function print(int $invoice)
    {
        return view('kudia.invoice', ['invoice' => $this->findInvoice($invoice)]);
    }

    public function pdf(int $invoice, DocumentExportService $exports)
    {
        $record = $this->findInvoice($invoice);

        return $exports->pdf('kudia.invoice', ['invoice' => $record], $record->invoice_no.'.pdf', showSystemFooter: false);
    }

    private function form(?ExternalInvoice $invoice = null)
    {
        return view('kudia.form', [
            'invoice' => $invoice,
            'customers' => Customer::where('company_id', $this->companyId())->forAccountGroup(Customer::GROUP_KUDIA)->orderBy('name')->get(),
            'currency' => $this->currency(),
        ]);
    }

    private function validateInvoice(Request $request): array
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where('company_id', $this->companyId())->where('account_group', Customer::GROUP_KUDIA)->whereNull('deleted_at')],
            'invoice_date' => ['required', 'date_format:Y-m-d'],
            'tax_rate' => ['required', 'numeric', 'decimal:0,2', 'between:0,100'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.details' => ['nullable', 'string', 'max:500'],
            'items.*.unit' => ['nullable', 'string', 'max:40'],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:1000000'],
            'items.*.unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000000'],
            'payment_info' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        // Keep invoices in the ledger's currency; no implicit exchange rates.
        $data['currency'] = $this->currency();

        return $data;
    }

    private function companyId(): int
    {
        abort_unless(auth()->user()?->company_id, 403);

        return (int) auth()->user()->company_id;
    }

    private function currency(): string
    {
        return Setting::where('company_id', $this->companyId())->value('currency') ?: 'IQD';
    }

    private function invoices()
    {
        return ExternalInvoice::where('company_id', $this->companyId())->where('provider', 'kudia');
    }

    private function findInvoice(int $invoice): ExternalInvoice
    {
        return $this->invoices()->with('customer')->findOrFail($invoice);
    }
}
