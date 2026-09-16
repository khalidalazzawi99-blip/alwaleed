<?php

namespace App\Http\Controllers;

use App\Exports\ArrayExport;
use App\Models\Cashbox;
use App\Models\CashboxLog;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Supplier;
use App\Services\DocumentExportService;
use App\Services\VoucherCodeService;
use App\Services\VoucherNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReceiptController extends Controller
{
    public function __construct(private VoucherNumberService $numbers) {}

    public function index()
    {
        $companyId = auth()->user()->company_id;
        $year = now()->year;

        return view('receipts.index', [
            'receipts' => Receipt::with(['customer', 'supplier', 'cashbox'])->where('company_id', $companyId)->latest()->get(),
            'customers' => Customer::where('company_id', $companyId)->orderBy('name')->get(),
            'suppliers' => Supplier::where('company_id', $companyId)->orderBy('name')->get(),
            'cashboxes' => Cashbox::operational()->where('company_id', $companyId)->where('is_active', true)->orderBy('id')->get(),
            'nextReceiptNo' => $this->numbers->preview($companyId, 'receipt', $year),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $data = $request->validate([
            'receipt_date' => ['required', 'date'], 'party_type' => ['required', 'in:customer,supplier'],
            'party_id' => ['required', 'integer'], 'cashbox_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'], 'notes' => ['nullable', 'string'],
        ]);
        $party = $this->findParty($data['party_type'], $data['party_id'], $companyId);
        $selectedCashbox = $this->findCashbox($data['cashbox_id'], $companyId);

        DB::transaction(function () use ($companyId, $party, $data, $selectedCashbox) {
            $receipt = Receipt::create([
                'company_id' => $companyId, 'cashbox_id' => $selectedCashbox->id,
                'receipt_no' => $this->numbers->next($companyId, 'receipt', (int) date('Y', strtotime($data['receipt_date']))),
                'receipt_date' => $data['receipt_date'],
                'customer_id' => $data['party_type'] === 'customer' ? $party->id : null,
                'supplier_id' => $data['party_type'] === 'supplier' ? $party->id : null,
                'amount' => $data['amount'], 'notes' => $data['notes'] ?? null,
            ]);
            $cashbox = Cashbox::whereKey($selectedCashbox->id)->lockForUpdate()->firstOrFail();
            $cashbox->increment('balance', $data['amount']);
            $this->log($cashbox->fresh(), $receipt, $party->name, 'قبض', $data['notes'] ?? null);
        });

        return redirect('/receipts')->with('success', 'تم إضافة سند القبض بنجاح');
    }

    public function edit(Receipt $receipt)
    {
        $this->ensureCompany($receipt);
        abort_if($receipt->status === 'cancelled', 422, 'لا يمكن تعديل سند ملغي.');
        $companyId = auth()->user()->company_id;

        return view('receipts.edit', [
            'receipt' => $receipt,
            'customers' => Customer::where('company_id', $companyId)->orderBy('name')->get(),
            'suppliers' => Supplier::where('company_id', $companyId)->orderBy('name')->get(),
            'cashboxes' => Cashbox::operational()->where('company_id', $companyId)
                ->where(fn ($q) => $q->where('is_active', true)->orWhereKey($receipt->cashbox_id))->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Receipt $receipt)
    {
        $this->ensureCompany($receipt);
        abort_if($receipt->status === 'cancelled', 422, 'لا يمكن تعديل سند ملغي.');
        $companyId = auth()->user()->company_id;
        $data = $request->validate([
            'receipt_date' => ['required', 'date'], 'party_type' => ['required', 'in:customer,supplier'],
            'party_id' => ['required', 'integer'], 'cashbox_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'], 'notes' => ['nullable', 'string'],
        ]);
        $party = $this->findParty($data['party_type'], $data['party_id'], $companyId);
        $selectedCashbox = $this->findCashbox($data['cashbox_id'], $companyId);

        DB::transaction(function () use ($receipt, $party, $data, $selectedCashbox, $companyId) {
            $oldAmount = (float) $receipt->amount;
            $oldCashboxId = $receipt->cashbox_id;
            $receipt->update([
                'cashbox_id' => $selectedCashbox->id, 'receipt_date' => $data['receipt_date'],
                'customer_id' => $data['party_type'] === 'customer' ? $party->id : null,
                'supplier_id' => $data['party_type'] === 'supplier' ? $party->id : null,
                'amount' => $data['amount'], 'notes' => $data['notes'] ?? null, 'updated_by' => auth()->id(),
            ]);
            $cashboxes = Cashbox::withTrashed()->where('company_id', $companyId)
                ->whereIn('id', array_unique([$oldCashboxId, $selectedCashbox->id]))->lockForUpdate()->get()->keyBy('id');
            $old = $cashboxes->get($oldCashboxId);
            $current = $cashboxes->get($selectedCashbox->id);
            if ($oldCashboxId === $selectedCashbox->id) {
                $current->increment('balance', (float) $data['amount'] - $oldAmount);
            } else {
                $old?->decrement('balance', $oldAmount);
                $current->increment('balance', $data['amount']);
            }
            $this->log($current->fresh(), $receipt, $party->name, 'تعديل قبض', 'تم تعديل سند قبض');
        });

        return redirect('/receipts')->with('success', 'تم تعديل سند القبض بنجاح');
    }

    public function destroy(Request $request, Receipt $receipt)
    {
        $this->ensureCompany($receipt);
        $data = $request->validate(['cancellation_reason' => ['nullable', 'string', 'max:1000']]);
        if ($receipt->status === 'cancelled') {
            return back()->with('success', 'السند ملغي مسبقًا.');
        }
        DB::transaction(function () use ($receipt, $data) {
            $cashbox = Cashbox::withTrashed()->whereKey($receipt->cashbox_id)->where('company_id', $receipt->company_id)->lockForUpdate()->first();
            $cashbox?->decrement('balance', $receipt->amount);
            if ($cashbox) {
                $this->log($cashbox->fresh(), $receipt, $receipt->party?->name ?? '-', 'إلغاء قبض', $data['cancellation_reason'] ?? 'تم إلغاء سند قبض');
            }
            $receipt->update(['status' => 'cancelled', 'cancelled_by' => auth()->id(), 'cancelled_at' => now(), 'cancellation_reason' => $data['cancellation_reason'] ?? null]);
        });

        return redirect('/receipts')->with('success', 'تم إلغاء سند القبض مع الاحتفاظ بسجل التدقيق.');
    }

    public function print($id)
    {
        $receipt = $this->findCompanyReceipt($id);

        return view('receipts.print', $this->printData($receipt));
    }

    public function pdf($id, DocumentExportService $exports)
    {
        $receipt = $this->findCompanyReceipt($id);

        return $exports->pdf('receipts.print', $this->printData($receipt), 'receipt-'.$receipt->receipt_no.'.pdf', 'landscape');
    }

    public function excel($id)
    {
        $receipt = $this->findCompanyReceipt($id);

        return Excel::download(new ArrayExport([__('messages.reference'), __('messages.date'), __('messages.party'), __('messages.amount'), __('messages.notes')], [[$receipt->receipt_no, $receipt->receipt_date, $receipt->party?->name, $receipt->amount, $receipt->notes]], 'سند قبض', $receipt->company_id), 'receipt-'.$receipt->receipt_no.'.xlsx');
    }

    private function printData(Receipt $receipt): array
    {
        $codes = app(VoucherCodeService::class);

        return ['receipt' => $receipt->loadMissing(['company', 'cashbox', 'customer', 'supplier']), 'qrCode' => $codes->qrDataUri(route('documents.verify', $receipt->verification_token)), 'barcode' => $codes->barcodeDataUri($receipt->receipt_no)];
    }

    private function findCompanyReceipt($id): Receipt
    {
        return Receipt::with(['customer', 'supplier'])->whereKey($id)->where('company_id', auth()->user()->company_id)->firstOrFail();
    }

    private function ensureCompany(Receipt $receipt): void
    {
        abort_unless((int) $receipt->company_id === (int) auth()->user()->company_id, 403);
    }

    private function findParty(string $type, int $id, int $companyId)
    {
        $model = $type === 'customer' ? Customer::class : Supplier::class;

        return $model::whereKey($id)->where('company_id', $companyId)->firstOrFail();
    }

    private function findCashbox(int $id, int $companyId): Cashbox
    {
        return Cashbox::operational()->whereKey($id)->where('company_id', $companyId)->where('is_active', true)->firstOrFail();
    }

    private function log(Cashbox $cashbox, Receipt $receipt, string $party, string $type, ?string $notes): void
    {
        CashboxLog::create(['company_id' => $receipt->company_id, 'cashbox_id' => $cashbox->id, 'type' => $type, 'reference_no' => $receipt->receipt_no, 'person_name' => $party, 'amount' => $receipt->amount, 'balance_after' => $cashbox->balance, 'notes' => $notes]);
    }
}
