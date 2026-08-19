<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receipt;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Cashbox;
use App\Models\CashboxLog;
use Illuminate\Support\Facades\DB;
use App\Exports\ArrayExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReceiptController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $receipts = Receipt::with(['customer', 'supplier'])->where('company_id', $companyId)
            ->latest()
            ->get();

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::where('company_id', $companyId)->orderBy('name')->get();

        $year = now()->year;
        $nextReceiptNumbers = $customers->mapWithKeys(fn ($party) => [
            'customer:'.$party->id => $this->nextReceiptNumber($companyId, 'customer', $party->id, $year),
        ])->toBase()->merge($suppliers->mapWithKeys(fn ($party) => [
            'supplier:'.$party->id => $this->nextReceiptNumber($companyId, 'supplier', $party->id, $year),
        ])->toBase());

        return view('receipts.index', [
            'customers' => $customers,
            'suppliers' => $suppliers,
            'receipts' => $receipts,
            'nextReceiptNo' => "RCP-{$year}-000001",
            'nextReceiptNumbers' => $nextReceiptNumbers,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $request->validate([
            'receipt_date' => ['required', 'date'],
            'party_type' => ['required', 'in:customer,supplier'],
            'party_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        $party = $this->findParty($request->party_type, $request->party_id, $companyId);

        DB::transaction(function () use ($companyId, $party, $request) {
            $party::whereKey($party->id)->lockForUpdate()->firstOrFail();
            Receipt::where('company_id', $companyId)
                ->where($request->party_type.'_id', $party->id)
                ->lockForUpdate()
                ->get(['id']);

            $receipt = Receipt::create([
                'company_id' => $companyId,
                'receipt_no' => $this->nextReceiptNumber($companyId, $request->party_type, $party->id, (int) date('Y', strtotime($request->receipt_date))),
                'receipt_date' => $request->receipt_date,
                'customer_id' => $request->party_type === 'customer' ? $party->id : null,
                'supplier_id' => $request->party_type === 'supplier' ? $party->id : null,
                'amount' => $request->amount,
                'notes' => $request->notes,
            ]);

            $cashbox = Cashbox::firstOrCreate(['company_id' => $companyId], ['balance' => 0]);
            $cashbox->increment('balance', $request->amount);
            $cashbox->refresh();

            CashboxLog::create([
                'company_id' => $companyId,
                'type' => 'قبض',
                'reference_no' => $receipt->receipt_no,
                'person_name' => $party->name,
                'amount' => $request->amount,
                'balance_after' => $cashbox->balance,
                'notes' => $request->notes,
            ]);
        });

        return redirect('/receipts')
            ->with('success', __('تم إضافة سند القبض بنجاح'));
    }

    public function edit(Receipt $receipt)
    {
        $companyId = auth()->user()->company_id;

        $this->ensureReceiptBelongsToCompany($receipt);

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('name')
            ->get();
        $suppliers = Supplier::where('company_id', $companyId)->orderBy('name')->get();

        return view('receipts.edit', [
            'receipt' => $receipt,
            'customers' => $customers,
            'suppliers' => $suppliers,
        ]);
    }

    public function update(Request $request, Receipt $receipt)
    {
        $companyId = auth()->user()->company_id;

        $this->ensureReceiptBelongsToCompany($receipt);

        $request->validate([
            'receipt_date' => ['required', 'date'],
            'party_type' => ['required', 'in:customer,supplier'],
            'party_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        $party = $this->findParty($request->party_type, $request->party_id, $companyId);

        $oldAmount = $receipt->amount;

        $partyOrYearChanged = $receipt->party_type !== $request->party_type
            || (int) $receipt->{$request->party_type.'_id'} !== (int) $party->id
            || (int) date('Y', strtotime($receipt->receipt_date)) !== (int) date('Y', strtotime($request->receipt_date));

        $receipt->update([
            'receipt_date' => $request->receipt_date,
            'customer_id' => $request->party_type === 'customer' ? $party->id : null,
            'supplier_id' => $request->party_type === 'supplier' ? $party->id : null,
            'amount' => $request->amount,
            'notes' => $request->notes,
            'receipt_no' => $partyOrYearChanged
                ? $this->nextReceiptNumber($companyId, $request->party_type, $party->id, (int) date('Y', strtotime($request->receipt_date)))
                : $receipt->receipt_no,
        ]);

        $cashbox = Cashbox::firstOrCreate(
            ['company_id' => $companyId],
            ['balance' => 0]
        );

        $cashbox->balance =
            $cashbox->balance - $oldAmount + $request->amount;

        $cashbox->save();

        CashboxLog::create([
            'company_id' => $companyId,
            'type' => 'تعديل قبض',
            'reference_no' => $receipt->receipt_no,
            'person_name' => $party->name,
            'amount' => $request->amount,
            'balance_after' => $cashbox->balance,
            'notes' => 'تم تعديل سند قبض',
        ]);

        return redirect('/receipts')
            ->with('success', __('تم تعديل سند القبض بنجاح'));
    }

    public function destroy(Receipt $receipt)
    {
        $companyId = auth()->user()->company_id;

        $this->ensureReceiptBelongsToCompany($receipt);

        $cashbox = Cashbox::firstOrCreate(
            ['company_id' => $companyId],
            ['balance' => 0]
        );

        $cashbox->balance -= $receipt->amount;
        $cashbox->save();

        CashboxLog::create([
            'company_id' => $companyId,
            'type' => 'حذف قبض',
            'reference_no' => $receipt->receipt_no,
            'person_name' => $receipt->party?->name ?? '-',
            'amount' => $receipt->amount,
            'balance_after' => $cashbox->balance,
            'notes' => 'تم حذف سند قبض',
        ]);

        $receipt->delete();

        return redirect('/receipts')
            ->with('success', __('تم حذف سند القبض'));
    }

    public function print($id)
    {
        $companyId = auth()->user()->company_id;

        $receipt = Receipt::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        return view('receipts.print', compact('receipt'));
    }

    public function pdf($id)
    {
        $receipt = $this->findCompanyReceipt($id);

        return Pdf::loadView('receipts.print', compact('receipt'))
            ->setPaper('a4')
            ->download('receipt-'.$receipt->receipt_no.'.pdf');
    }

    public function excel($id)
    {
        $receipt = $this->findCompanyReceipt($id);
        $export = new ArrayExport(
            [__('messages.reference'), __('messages.date'), __('messages.customer'), __('messages.amount'), __('messages.notes')],
            [[$receipt->receipt_no, $receipt->receipt_date, $receipt->party?->name, $receipt->amount, $receipt->notes]],
        );

        return Excel::download($export, 'receipt-'.$receipt->receipt_no.'.xlsx');
    }

    private function findCompanyReceipt($id): Receipt
    {
        return Receipt::with(['customer', 'supplier'])
            ->where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();
    }

    private function ensureReceiptBelongsToCompany(Receipt $receipt)
    {
        if ($receipt->company_id !== auth()->user()->company_id) {
            abort(403, __('غير مسموح بالوصول إلى هذا السند'));
        }
    }

    private function findParty(string $type, int $id, int $companyId)
    {
        $model = $type === 'customer' ? Customer::class : Supplier::class;
        return $model::whereKey($id)->where('company_id', $companyId)->firstOrFail();
    }

    private function nextReceiptNumber(int $companyId, string $partyType, int $partyId, int $year): string
    {
        $prefix = "RCP-{$year}-";
        $lastSequence = Receipt::where('company_id', $companyId)
            ->where($partyType.'_id', $partyId)
            ->where('receipt_no', 'like', $prefix.'%')
            ->pluck('receipt_no')
            ->map(fn ($number) => (int) substr($number, -6))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($lastSequence + 1), 6, '0', STR_PAD_LEFT);
    }
}
