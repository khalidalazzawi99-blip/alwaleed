<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Setting;
use App\Exports\ArrayExport;
use App\Services\DocumentExportService;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $suppliers = Supplier::where('company_id', $companyId)
            ->latest()
            ->get();

        return view('suppliers.index', compact('suppliers'));
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

        Supplier::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        return redirect('/suppliers')
            ->with('success', __('تم إضافة المورد بنجاح'));
    }

    public function show(Request $request, Supplier $supplier)
    {
        $this->ensureSupplierBelongsToCompany($supplier);

        return view('suppliers.show', $this->statementData($request, $supplier));
    }

    public function print(Request $request, Supplier $supplier)
    {
        $this->ensureSupplierBelongsToCompany($supplier);

        return view('suppliers.print', $this->statementData($request, $supplier));
    }

    public function edit(Supplier $supplier)
    {
        $this->ensureSupplierBelongsToCompany($supplier);

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->ensureSupplierBelongsToCompany($supplier);
        $supplier->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]));

        return redirect('/suppliers')->with('success', __('تم تعديل بيانات المورد بنجاح'));
    }

    public function pdf(Request $request, Supplier $supplier, DocumentExportService $exports)
    {
        $this->ensureSupplierBelongsToCompany($supplier);
        $data = $this->statementData($request, $supplier);

        return $exports->pdf('suppliers.print', $data, 'supplier-statement-'.$supplier->id.'.pdf');
    }

    public function excel(Request $request, Supplier $supplier)
    {
        $this->ensureSupplierBelongsToCompany($supplier);
        $data = $this->statementData($request, $supplier);

        return Excel::download($this->statementExport($data), 'supplier-statement-'.$supplier->id.'.xlsx');
    }

    private function statementData(Request $request, Supplier $supplier): array
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $payments = Payment::where('company_id', $supplier->company_id)
            ->where('supplier_id', $supplier->id)
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('payment_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('payment_date', '<=', $date))
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $receipts = Receipt::where('company_id', $supplier->company_id)
            ->where('supplier_id', $supplier->id)
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('receipt_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('receipt_date', '<=', $date))
            ->get();

        $runningBalance = 0;
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
                $runningBalance += $movement->received - $movement->paid;
                $movement->balance = $runningBalance;
                return $movement;
            });

        return [
            'supplier' => $supplier,
            'payments' => $payments,
            'receipts' => $receipts,
            'movements' => $movements,
            'totalReceived' => $receipts->sum('amount'),
            'totalPaid' => $payments->sum('amount'),
            'totalInvoiced' => 0,
            'balance' => $runningBalance,
            'movementsCount' => $movements->count(),
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
            'company' => $supplier->company,
            'setting' => Setting::where('company_id', $supplier->company_id)->first(),
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
        ], $rows, 'كشف حساب المورد — '.$data['supplier']->name, $data['supplier']->company_id);
    }

    private function ensureSupplierBelongsToCompany(Supplier $supplier)
    {
        if ($supplier->company_id !== auth()->user()->company_id) {
            abort(403, __('غير مسموح بالوصول إلى هذا المورد'));
        }
    }
}
