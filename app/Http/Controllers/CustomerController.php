<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Payment;
use App\Models\ExternalInvoice;
use App\Models\Setting;
use App\Exports\ArrayExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $customers = Customer::where('company_id', $companyId)
            ->latest()
            ->get();

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        Customer::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        return redirect('/customers')
            ->with('success', __('تم إضافة الزبون بنجاح'));
    }

    public function show(Request $request, Customer $customer)
    {
        $this->ensureCustomerBelongsToCompany($customer);
        $data = $this->statementData($request, $customer);
        $data['externalInvoicesPage'] = ExternalInvoice::where('company_id', $customer->company_id)
            ->where('customer_id', $customer->id)->latest('invoice_date')->latest('id')->paginate(15)->withQueryString();
        return view('customers.show', $data);
    }

    public function destroy(Customer $customer)
    {
        $this->ensureCustomerBelongsToCompany($customer);

        $customer->delete();

        return redirect('/customers')
            ->with('success', __('تم حذف الزبون بنجاح'));
    }

    public function print(Request $request, Customer $customer)
    {
        $this->ensureCustomerBelongsToCompany($customer);

        return view('customers.print', $this->statementData($request, $customer));
    }

    public function pdf(Request $request, Customer $customer)
    {
        $this->ensureCustomerBelongsToCompany($customer);
        $data = $this->statementData($request, $customer);

        return Pdf::loadView('customers.print', $data + ['pdfMode' => true])
            ->setPaper('a4')
            ->download('customer-statement-'.$customer->id.'.pdf');
    }

    public function excel(Request $request, Customer $customer)
    {
        $this->ensureCustomerBelongsToCompany($customer);
        $data = $this->statementData($request, $customer);

        return Excel::download($this->statementExport($data), 'customer-statement-'.$customer->id.'.xlsx');
    }

    private function statementData(Request $request, Customer $customer): array
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $receipts = Receipt::where('company_id', $customer->company_id)
            ->where('customer_id', $customer->id)
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('receipt_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('receipt_date', '<=', $date))
            ->orderBy('receipt_date')
            ->orderBy('id')
            ->get();

        $payments = Payment::where('company_id', $customer->company_id)
            ->where('customer_id', $customer->id)
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('payment_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('payment_date', '<=', $date))
            ->get();

        $externalInvoices = ExternalInvoice::where('company_id', $customer->company_id)->where('customer_id', $customer->id)
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('invoice_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('invoice_date', '<=', $date));
        $totalInvoices = (float) (clone $externalInvoices)->sum('amount');

        $runningBalance = $totalInvoices;
        $movements = $receipts->map(fn (Receipt $receipt) => (object) [
                'number' => $receipt->receipt_no,
                'date' => $receipt->receipt_date,
                'sort_id' => $receipt->id,
                'type' => __('قبض'),
                'invoiced' => 0,
                'received' => (float) $receipt->amount,
                'paid' => 0,
                'notes' => $receipt->notes,
            ])->concat($payments->map(fn (Payment $payment) => (object) [
                'number' => $payment->payment_no,
                'date' => $payment->payment_date,
                'sort_id' => $payment->id,
                'type' => __('صرف'),
                'invoiced' => 0,
                'received' => 0,
                'paid' => (float) $payment->amount,
                'notes' => $payment->notes,
            ]))->sortBy(fn ($movement) => $movement->date.'-'.str_pad($movement->sort_id, 12, '0', STR_PAD_LEFT))->values()
            ->map(function ($movement) use (&$runningBalance) {
                $runningBalance -= $movement->received;
                $movement->balance = $runningBalance;
                return $movement;
            });

        return [
            'customer' => $customer,
            'receipts' => $receipts,
            'payments' => $payments,
            'movements' => $movements,
            'totalReceived' => $receipts->sum('amount'),
            'totalPaid' => $payments->sum('amount'),
            'totalInvoiced' => $totalInvoices,
            'balance' => $totalInvoices - (float) $receipts->sum('amount'),
            'movementsCount' => $movements->count(),
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
            'company' => $customer->company,
            'setting' => Setting::where('company_id', $customer->company_id)->first(),
        ];
    }

    private function statementExport(array $data): ArrayExport
    {
        $rows = $data['movements']->map(fn ($movement, $index) => [
            $index + 1, $movement->date, $movement->number, $movement->type,
            $movement->invoiced, $movement->received, $movement->paid, $movement->balance, $movement->notes,
        ])->all();

        $rows[] = ['', '', '', __('messages.total'), $data['totalInvoiced'], $data['totalReceived'], $data['totalPaid'], $data['balance'], ''];

        return new ArrayExport([
            '#', __('messages.date'), __('messages.reference'), __('messages.movement_type'),
            __('messages.invoiced'), __('messages.received'), __('messages.paid'), __('messages.running_balance'), __('messages.notes'),
        ], $rows);
    }

    private function ensureCustomerBelongsToCompany(Customer $customer)
    {
        if ($customer->company_id !== auth()->user()->company_id) {
            abort(403, __('غير مسموح بالوصول إلى هذا الزبون'));
        }
    }
}
