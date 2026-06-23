<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\Cashbox;
use App\Models\CashboxLog;

class PaymentController extends Controller
{
    public function index()
    {
        return view('payments.index', [
            'suppliers' => Supplier::all(),
            'payments' => Payment::latest()->get(),
            'nextPaymentNo' => str_pad(Payment::count() + 1, 6, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request)
    {
        $payment = Payment::create([
            'payment_no' => str_pad(Payment::count() + 1, 6, '0', STR_PAD_LEFT),
            'payment_date' => $request->payment_date,
            'supplier_id' => $request->supplier_id,
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        $cashbox = Cashbox::first();

        if ($cashbox) {
            $cashbox->balance -= $request->amount;
            $cashbox->save();

            CashboxLog::create([
                'type' => 'صرف',
                'reference_no' => $payment->payment_no,
                'person_name' => $payment->supplier->name ?? '-',
                'amount' => $request->amount,
                'balance_after' => $cashbox->balance,
                'notes' => $request->notes,
            ]);
        }

        return redirect('/payments');
    }

    public function edit(Payment $payment)
    {
        return view('payments.edit', [
            'payment' => $payment,
            'suppliers' => Supplier::all(),
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $oldAmount = $payment->amount;

        $payment->update([
            'payment_date' => $request->payment_date,
            'supplier_id' => $request->supplier_id,
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        $cashbox = Cashbox::first();

        if ($cashbox) {
            $cashbox->balance = $cashbox->balance + $oldAmount - $request->amount;
            $cashbox->save();

            CashboxLog::create([
                'type' => 'تعديل صرف',
                'reference_no' => $payment->payment_no,
                'person_name' => $payment->supplier->name ?? '-',
                'amount' => $request->amount,
                'balance_after' => $cashbox->balance,
                'notes' => 'تم تعديل سند صرف',
            ]);
        }

        return redirect('/payments');
    }

    public function destroy(Payment $payment)
    {
        $cashbox = Cashbox::first();

        if ($cashbox) {
            $cashbox->balance += $payment->amount;
            $cashbox->save();

            CashboxLog::create([
                'type' => 'حذف صرف',
                'reference_no' => $payment->payment_no,
                'person_name' => $payment->supplier->name ?? '-',
                'amount' => $payment->amount,
                'balance_after' => $cashbox->balance,
                'notes' => 'تم حذف سند صرف',
            ]);
        }

        $payment->delete();

        return redirect('/payments');
    }

    public function print($id)
    {
        $payment = Payment::findOrFail($id);

        return view('payments.print', compact('payment'));
    }
}