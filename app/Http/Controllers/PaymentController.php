<?php

namespace App\Http\Controllers;

use App\Exports\ArrayExport;
use App\Models\Cashbox;
use App\Models\CashboxLog;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Supplier;
use App\Services\DocumentExportService;
use App\Services\VoucherCodeService;
use App\Services\VoucherNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    public function __construct(private VoucherNumberService $numbers) {}

    public function index()
    {
        $companyId = auth()->user()->company_id;

        return view('payments.index', [
            'payments' => Payment::with(['customer', 'supplier', 'cashbox'])->where('company_id', $companyId)->latest()->get(),
            'customers' => Customer::where('company_id', $companyId)->orderBy('name')->get(),
            'suppliers' => Supplier::where('company_id', $companyId)->orderBy('name')->get(),
            'cashboxes' => Cashbox::operational()->where('company_id', $companyId)->where('is_active', true)->orderBy('id')->get(),
            'nextPaymentNo' => $this->numbers->preview($companyId, 'payment', now()->year),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $data = $request->validate([
            'payment_date' => ['required', 'date'], 'party_type' => ['required', 'in:customer,supplier'], 'party_id' => ['required', 'integer'],
            'cashbox_id' => ['required', 'integer'], 'amount' => ['required', 'numeric', 'min:0.01'], 'notes' => ['nullable', 'string'],
        ]);
        $party = $this->findParty($data['party_type'], $data['party_id'], $companyId);
        $selectedCashbox = $this->findCashbox($data['cashbox_id'], $companyId);
        DB::transaction(function () use ($companyId, $party, $data, $selectedCashbox) {
            $payment = Payment::create([
                'company_id' => $companyId, 'cashbox_id' => $selectedCashbox->id,
                'payment_no' => $this->numbers->next($companyId, 'payment', (int) date('Y', strtotime($data['payment_date']))),
                'payment_date' => $data['payment_date'], 'supplier_id' => $data['party_type'] === 'supplier' ? $party->id : null,
                'customer_id' => $data['party_type'] === 'customer' ? $party->id : null, 'amount' => $data['amount'], 'notes' => $data['notes'] ?? null,
            ]);
            $cashbox = Cashbox::whereKey($selectedCashbox->id)->lockForUpdate()->firstOrFail();
            $cashbox->decrement('balance', $data['amount']);
            $this->log($cashbox->fresh(), $payment, $party->name, 'صرف', $data['notes'] ?? null);
        });

        return redirect('/payments')->with('success', 'تم إضافة سند الصرف بنجاح');
    }

    public function edit(Payment $payment)
    {
        $this->ensureCompany($payment);
        abort_if($payment->status === 'cancelled', 422, 'لا يمكن تعديل سند ملغي.');
        $companyId = auth()->user()->company_id;

        return view('payments.edit', [
            'payment' => $payment, 'suppliers' => Supplier::where('company_id', $companyId)->orderBy('name')->get(),
            'customers' => Customer::where('company_id', $companyId)->orderBy('name')->get(),
            'cashboxes' => Cashbox::operational()->where('company_id', $companyId)->where(fn ($q) => $q->where('is_active', true)->orWhereKey($payment->cashbox_id))->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $this->ensureCompany($payment);
        abort_if($payment->status === 'cancelled', 422, 'لا يمكن تعديل سند ملغي.');
        $companyId = auth()->user()->company_id;
        $data = $request->validate([
            'payment_date' => ['required', 'date'], 'party_type' => ['required', 'in:customer,supplier'], 'party_id' => ['required', 'integer'],
            'cashbox_id' => ['required', 'integer'], 'amount' => ['required', 'numeric', 'min:0.01'], 'notes' => ['nullable', 'string'],
        ]);
        $party = $this->findParty($data['party_type'], $data['party_id'], $companyId);
        $selectedCashbox = $this->findCashbox($data['cashbox_id'], $companyId);
        DB::transaction(function () use ($payment, $party, $data, $selectedCashbox, $companyId) {
            $oldAmount = (float) $payment->amount;
            $oldCashboxId = $payment->cashbox_id;
            $payment->update(['cashbox_id' => $selectedCashbox->id, 'payment_date' => $data['payment_date'],
                'supplier_id' => $data['party_type'] === 'supplier' ? $party->id : null, 'customer_id' => $data['party_type'] === 'customer' ? $party->id : null,
                'amount' => $data['amount'], 'notes' => $data['notes'] ?? null, 'updated_by' => auth()->id()]);
            $cashboxes = Cashbox::withTrashed()->where('company_id', $companyId)->whereIn('id', array_unique([$oldCashboxId, $selectedCashbox->id]))->lockForUpdate()->get()->keyBy('id');
            $old = $cashboxes->get($oldCashboxId);
            $current = $cashboxes->get($selectedCashbox->id);
            if ($oldCashboxId === $selectedCashbox->id) {
                $current->decrement('balance', (float) $data['amount'] - $oldAmount);
            } else {
                $old?->increment('balance', $oldAmount);
                $current->decrement('balance', $data['amount']);
            }
            $this->log($current->fresh(), $payment, $party->name, 'تعديل صرف', 'تم تعديل سند صرف');
        });

        return redirect('/payments')->with('success', 'تم تعديل سند الصرف بنجاح');
    }

    public function destroy(Request $request, Payment $payment)
    {
        $this->ensureCompany($payment);
        $data = $request->validate(['cancellation_reason' => ['nullable', 'string', 'max:1000']]);
        if ($payment->status === 'cancelled') {
            return back()->with('success', 'السند ملغي مسبقًا.');
        }
        DB::transaction(function () use ($payment, $data) {
            $cashbox = Cashbox::withTrashed()->whereKey($payment->cashbox_id)->where('company_id', $payment->company_id)->lockForUpdate()->first();
            $cashbox?->increment('balance', $payment->amount);
            if ($cashbox) {
                $this->log($cashbox->fresh(), $payment, $payment->party?->name ?? '-', 'إلغاء صرف', $data['cancellation_reason'] ?? 'تم إلغاء سند صرف');
            }
            $payment->update(['status' => 'cancelled', 'cancelled_by' => auth()->id(), 'cancelled_at' => now(), 'cancellation_reason' => $data['cancellation_reason'] ?? null]);
        });

        return redirect('/payments')->with('success', 'تم إلغاء سند الصرف مع الاحتفاظ بسجل التدقيق.');
    }

    public function print($id)
    {
        $payment = $this->findCompanyPayment($id);

        return view('payments.print', $this->printData($payment));
    }

    public function pdf($id, DocumentExportService $exports)
    {
        $payment = $this->findCompanyPayment($id);

        return $exports->pdf('payments.print', $this->printData($payment), 'payment-'.$payment->payment_no.'.pdf', 'landscape');
    }

    public function excel($id)
    {
        $payment = $this->findCompanyPayment($id);

        return Excel::download(new ArrayExport([__('messages.reference'), __('messages.date'), __('messages.party'), __('messages.amount'), __('messages.notes')], [[$payment->payment_no, $payment->payment_date, $payment->party?->name, $payment->amount, $payment->notes]], 'سند صرف', $payment->company_id), 'payment-'.$payment->payment_no.'.xlsx');
    }

    private function printData(Payment $payment): array
    {
        $codes = app(VoucherCodeService::class);

        return ['payment' => $payment->loadMissing(['company', 'cashbox', 'customer', 'supplier']), 'qrCode' => $codes->qrDataUri(route('documents.verify', $payment->verification_token)), 'barcode' => $codes->barcodeDataUri($payment->payment_no)];
    }

    private function findCompanyPayment($id): Payment
    {
        return Payment::with(['customer', 'supplier'])->whereKey($id)->where('company_id', auth()->user()->company_id)->firstOrFail();
    }

    private function ensureCompany(Payment $payment): void
    {
        abort_unless((int) $payment->company_id === (int) auth()->user()->company_id, 403);
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

    private function log(Cashbox $cashbox, Payment $payment, string $party, string $type, ?string $notes): void
    {
        CashboxLog::create(['company_id' => $payment->company_id, 'cashbox_id' => $cashbox->id, 'type' => $type, 'reference_no' => $payment->payment_no, 'person_name' => $party, 'amount' => $payment->amount, 'balance_after' => $cashbox->balance, 'notes' => $notes]);
    }
}
