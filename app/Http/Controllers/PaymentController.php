<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Cashbox;
use App\Models\CashboxLog;
use Illuminate\Support\Facades\DB;
use App\Exports\ArrayExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $payments = Payment::with(['customer', 'supplier'])->where('company_id', $companyId)
            ->latest()
            ->get();
        $customers = Customer::where('company_id', $companyId)->orderBy('name')->get();

        $suppliers = Supplier::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $year = now()->year;
        $nextPaymentNumbers = $suppliers->mapWithKeys(fn ($party) => [
            'supplier:'.$party->id => $this->nextPaymentNumber($companyId, 'supplier', $party->id, $year),
        ])->toBase()->merge($customers->mapWithKeys(fn ($party) => [
            'customer:'.$party->id => $this->nextPaymentNumber($companyId, 'customer', $party->id, $year),
        ])->toBase());

        return view('payments.index', [
            'suppliers' => $suppliers,
            'customers' => $customers,
            'payments' => $payments,
            'nextPaymentNo' => "PAY-{$year}-000001",
            'nextPaymentNumbers' => $nextPaymentNumbers,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $request->validate([
            'payment_date' => ['required', 'date'],
            'party_type' => ['required', 'in:customer,supplier'],
            'party_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        $party = $this->findParty($request->party_type, $request->party_id, $companyId);

        DB::transaction(function () use ($companyId, $party, $request) {
            $party::whereKey($party->id)->lockForUpdate()->firstOrFail();
            Payment::where('company_id', $companyId)
                ->where($request->party_type.'_id', $party->id)
                ->lockForUpdate()
                ->get(['id']);

            $payment = Payment::create([
                'company_id' => $companyId,
                'payment_no' => $this->nextPaymentNumber($companyId, $request->party_type, $party->id, (int) date('Y', strtotime($request->payment_date))),
                'payment_date' => $request->payment_date,
                'supplier_id' => $request->party_type === 'supplier' ? $party->id : null,
                'customer_id' => $request->party_type === 'customer' ? $party->id : null,
                'amount' => $request->amount,
                'notes' => $request->notes,
            ]);

            $cashbox = Cashbox::firstOrCreate(['company_id' => $companyId], ['balance' => 0]);
            $cashbox->decrement('balance', $request->amount);
            $cashbox->refresh();

            CashboxLog::create([
                'company_id' => $companyId,
                'type' => 'صرف',
                'reference_no' => $payment->payment_no,
                'person_name' => $party->name,
                'amount' => $request->amount,
                'balance_after' => $cashbox->balance,
                'notes' => $request->notes,
            ]);
        });

        return redirect('/payments')
            ->with('success', __('تم إضافة سند الصرف بنجاح'));
    }

    public function edit(Payment $payment)
    {
        $companyId = auth()->user()->company_id;

        $this->ensurePaymentBelongsToCompany($payment);

        $suppliers = Supplier::where('company_id', $companyId)
            ->orderBy('name')
            ->get();
        $customers = Customer::where('company_id', $companyId)->orderBy('name')->get();

        return view('payments.edit', [
            'payment' => $payment,
            'suppliers' => $suppliers,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $companyId = auth()->user()->company_id;

        $this->ensurePaymentBelongsToCompany($payment);

        $request->validate([
            'payment_date' => ['required', 'date'],
            'party_type' => ['required', 'in:customer,supplier'],
            'party_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        $party = $this->findParty($request->party_type, $request->party_id, $companyId);

        $oldAmount = $payment->amount;

        $partyOrYearChanged = $payment->party_type !== $request->party_type
            || (int) $payment->{$request->party_type.'_id'} !== (int) $party->id
            || (int) date('Y', strtotime($payment->payment_date)) !== (int) date('Y', strtotime($request->payment_date));

        $payment->update([
            'payment_date' => $request->payment_date,
            'supplier_id' => $request->party_type === 'supplier' ? $party->id : null,
            'customer_id' => $request->party_type === 'customer' ? $party->id : null,
            'amount' => $request->amount,
            'notes' => $request->notes,
            'payment_no' => $partyOrYearChanged
                ? $this->nextPaymentNumber($companyId, $request->party_type, $party->id, (int) date('Y', strtotime($request->payment_date)))
                : $payment->payment_no,
        ]);

        $cashbox = Cashbox::firstOrCreate(
            ['company_id' => $companyId],
            ['balance' => 0]
        );

        $cashbox->balance =
            $cashbox->balance + $oldAmount - $request->amount;

        $cashbox->save();

        CashboxLog::create([
            'company_id' => $companyId,
            'type' => 'تعديل صرف',
            'reference_no' => $payment->payment_no,
            'person_name' => $party->name,
            'amount' => $request->amount,
            'balance_after' => $cashbox->balance,
            'notes' => 'تم تعديل سند صرف',
        ]);

        return redirect('/payments')
            ->with('success', __('تم تعديل سند الصرف بنجاح'));
    }

    public function destroy(Payment $payment)
    {
        $companyId = auth()->user()->company_id;

        $this->ensurePaymentBelongsToCompany($payment);

        $cashbox = Cashbox::firstOrCreate(
            ['company_id' => $companyId],
            ['balance' => 0]
        );

        $cashbox->balance += $payment->amount;
        $cashbox->save();

        CashboxLog::create([
            'company_id' => $companyId,
            'type' => 'حذف صرف',
            'reference_no' => $payment->payment_no,
            'person_name' => $payment->party?->name ?? '-',
            'amount' => $payment->amount,
            'balance_after' => $cashbox->balance,
            'notes' => 'تم حذف سند صرف',
        ]);

        $payment->delete();

        return redirect('/payments')
            ->with('success', __('تم حذف سند الصرف'));
    }

    public function print($id)
    {
        $companyId = auth()->user()->company_id;

        $payment = Payment::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        return view('payments.print', compact('payment'));
    }

    public function pdf($id)
    {
        $payment = $this->findCompanyPayment($id);

        return Pdf::loadView('payments.print', compact('payment'))
            ->setPaper('a4')
            ->download('payment-'.$payment->payment_no.'.pdf');
    }

    public function excel($id)
    {
        $payment = $this->findCompanyPayment($id);
        $export = new ArrayExport(
            [__('messages.reference'), __('messages.date'), __('messages.supplier'), __('messages.amount'), __('messages.notes')],
            [[$payment->payment_no, $payment->payment_date, $payment->party?->name, $payment->amount, $payment->notes]],
        );

        return Excel::download($export, 'payment-'.$payment->payment_no.'.xlsx');
    }

    private function findCompanyPayment($id): Payment
    {
        return Payment::with(['customer', 'supplier'])
            ->where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();
    }

    private function ensurePaymentBelongsToCompany(Payment $payment)
    {
        if ($payment->company_id !== auth()->user()->company_id) {
            abort(403, __('غير مسموح بالوصول إلى هذا السند'));
        }
    }

    private function findParty(string $type, int $id, int $companyId)
    {
        $model = $type === 'customer' ? Customer::class : Supplier::class;
        return $model::whereKey($id)->where('company_id', $companyId)->firstOrFail();
    }

    private function nextPaymentNumber(int $companyId, string $partyType, int $partyId, int $year): string
    {
        $prefix = "PAY-{$year}-";
        $lastSequence = Payment::where('company_id', $companyId)
            ->where($partyType.'_id', $partyId)
            ->where('payment_no', 'like', $prefix.'%')
            ->pluck('payment_no')
            ->map(fn ($number) => (int) substr($number, -6))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($lastSequence + 1), 6, '0', STR_PAD_LEFT);
    }
}
