<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\CashboxLog;
use App\Models\Receipt;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'cashboxLogs' => CashboxLog::with('cashbox')->where('company_id', $companyId)
                ->whereIn('type', ['إيداع مباشر', 'سحب مباشر'])->latest()->get(),
            'totalReceipts' => Receipt::where('company_id', $companyId)->sum('amount')
                + CashboxLog::where('company_id', $companyId)->where('type', 'إيداع مباشر')->sum('amount'),
            'totalPayments' => Payment::where('company_id', $companyId)->sum('amount')
                + CashboxLog::where('company_id', $companyId)->where('type', 'سحب مباشر')->sum('amount'),
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

    public function transaction(Request $request, Cashbox $cashbox)
    {
        $this->ensureOwned($cashbox);
        abort_unless($cashbox->is_active, 422, 'الصندوق غير فعال');

        $data = $request->validate([
            'type' => ['required', 'in:deposit,withdrawal'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($cashbox, $data): void {
            $lockedCashbox = Cashbox::whereKey($cashbox->id)
                ->where('company_id', auth()->user()->company_id)
                ->lockForUpdate()->firstOrFail();
            $amount = (float) $data['amount'];

            if ($data['type'] === 'withdrawal' && $amount > (float) $lockedCashbox->balance) {
                throw ValidationException::withMessages([
                    'amount' => 'مبلغ السحب أكبر من رصيد الصندوق المتاح.',
                ]);
            }

            $data['type'] === 'deposit'
                ? $lockedCashbox->increment('balance', $amount)
                : $lockedCashbox->decrement('balance', $amount);
            $lockedCashbox->refresh();

            CashboxLog::create([
                'company_id' => $lockedCashbox->company_id,
                'cashbox_id' => $lockedCashbox->id,
                'type' => $data['type'] === 'deposit' ? 'إيداع مباشر' : 'سحب مباشر',
                'reference_no' => 'DIRECT-'.now()->format('YmdHis').'-'.$lockedCashbox->id,
                'person_name' => auth()->user()->name,
                'amount' => $amount,
                'balance_after' => $lockedCashbox->balance,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return back()->with('success', $data['type'] === 'deposit'
            ? 'تم الإيداع في الصندوق بنجاح'
            : 'تم السحب من الصندوق بنجاح');
    }

    private function ensureOwned(Cashbox $cashbox): void
    {
        abort_unless($cashbox->company_id === auth()->user()->company_id, 404);
    }
}
