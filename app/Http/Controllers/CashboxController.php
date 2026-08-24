<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\Receipt;
use App\Models\Payment;
use Illuminate\Http\Request;

class CashboxController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $cashboxes = Cashbox::where('company_id', $companyId)->orderBy('id')->get();
        return view('cashbox.index', [
            'cashboxes' => $cashboxes,
            'balance' => $cashboxes->sum('balance'),
            'receipts' => Receipt::with('cashbox')->where('company_id', $companyId)->latest()->get(),
            'payments' => Payment::with('cashbox')->where('company_id', $companyId)->latest()->get(),
            'totalReceipts' => Receipt::where('company_id', $companyId)->sum('amount'),
            'totalPayments' => Payment::where('company_id', $companyId)->sum('amount'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'balance' => ['nullable', 'numeric']]);
        Cashbox::create($data + ['company_id' => auth()->user()->company_id, 'is_active' => true]);
        return back()->with('success', 'تمت إضافة الصندوق');
    }

    public function update(Request $request, Cashbox $cashbox)
    {
        $this->ensureOwned($cashbox);
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'is_active' => ['nullable', 'boolean']]);
        $cashbox->update(['name' => $data['name'], 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'تم تحديث الصندوق');
    }

    public function destroy(Cashbox $cashbox)
    {
        $this->ensureOwned($cashbox);
        abort_if(Cashbox::where('company_id', $cashbox->company_id)->count() <= 1, 422, 'لا يمكن حذف الصندوق الوحيد');
        abort_if((float) $cashbox->balance !== 0.0, 422, 'يجب أن يكون رصيد الصندوق صفراً قبل حذفه');
        $cashbox->delete();
        return back()->with('success', 'تم حذف الصندوق');
    }

    private function ensureOwned(Cashbox $cashbox): void
    {
        abort_unless($cashbox->company_id === auth()->user()->company_id, 404);
    }
}
