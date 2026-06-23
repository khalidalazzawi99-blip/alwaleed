<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receipt;
use App\Models\Customer;
use App\Models\Cashbox;
use App\Models\CashboxLog;

class ReceiptController extends Controller
{
    public function index()
    {
        return view('receipts.index', [
            'customers' => Customer::all(),
            'receipts' => Receipt::latest()->get(),
            'nextReceiptNo' => str_pad(Receipt::count() + 1, 6, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request)
    {
        $receipt = Receipt::create([
            'receipt_no' => str_pad(Receipt::count() + 1, 6, '0', STR_PAD_LEFT),
            'receipt_date' => $request->receipt_date,
            'customer_id' => $request->customer_id,
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        $cashbox = Cashbox::first();

        if ($cashbox) {
            $cashbox->balance += $request->amount;
            $cashbox->save();

            CashboxLog::create([
                'type' => 'قبض',
                'reference_no' => $receipt->receipt_no,
                'person_name' => $receipt->customer->name ?? '-',
                'amount' => $request->amount,
                'balance_after' => $cashbox->balance,
                'notes' => $request->notes,
            ]);
        }

        return redirect('/receipts');
    }

    public function edit(Receipt $receipt)
    {
        return view('receipts.edit', [
            'receipt' => $receipt,
            'customers' => Customer::all(),
        ]);
    }

    public function update(Request $request, Receipt $receipt)
    {
        $oldAmount = $receipt->amount;

        $receipt->update([
            'receipt_date' => $request->receipt_date,
            'customer_id' => $request->customer_id,
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        $cashbox = Cashbox::first();

        if ($cashbox) {
            $cashbox->balance = $cashbox->balance - $oldAmount + $request->amount;
            $cashbox->save();

            CashboxLog::create([
                'type' => 'تعديل قبض',
                'reference_no' => $receipt->receipt_no,
                'person_name' => $receipt->customer->name ?? '-',
                'amount' => $request->amount,
                'balance_after' => $cashbox->balance,
                'notes' => 'تم تعديل سند قبض',
            ]);
        }

        return redirect('/receipts');
    }

    public function destroy(Receipt $receipt)
    {
        $cashbox = Cashbox::first();

        if ($cashbox) {
            $cashbox->balance -= $receipt->amount;
            $cashbox->save();

            CashboxLog::create([
                'type' => 'حذف قبض',
                'reference_no' => $receipt->receipt_no,
                'person_name' => $receipt->customer->name ?? '-',
                'amount' => $receipt->amount,
                'balance_after' => $cashbox->balance,
                'notes' => 'تم حذف سند قبض',
            ]);
        }

        $receipt->delete();

        return redirect('/receipts');
    }

    public function print($id)
    {
        $receipt = Receipt::findOrFail($id);

        return view('receipts.print', compact('receipt'));
    }
}