<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\CashboxLog;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use App\Services\CashboxStatementService;
use App\Services\VoucherNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CashboxController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $banksOnly = $request->routeIs('banks.index');
        $cashboxes = Cashbox::operational()->with('customers')->where('company_id', $companyId)
            ->when($banksOnly, fn ($query) => $query->where('account_type', 'bank'))->orderBy('id')->get();
        $request->validate(['cashbox_id' => ['nullable', 'integer']]);
        $selectedId = $request->filled('cashbox_id') ? $request->integer('cashbox_id') : null;
        abort_if($selectedId && ! $cashboxes->contains('id', $selectedId), 404);
        $visibleCashboxes = $selectedId ? $cashboxes->where('id', $selectedId) : $cashboxes;
        $filterAccounts = fn ($query) => $query->when($banksOnly || $selectedId,
            fn ($query) => $query->whereIn('cashbox_id', $visibleCashboxes->pluck('id')));
        $receipts = $filterAccounts(Receipt::with(['cashbox', 'customer', 'supplier'])->active()->where('company_id', $companyId))->latest()->get();
        $payments = $filterAccounts(Payment::with(['cashbox', 'customer', 'supplier'])->active()->where('company_id', $companyId))->latest()->get();
        $cashboxLogs = $filterAccounts(CashboxLog::with('cashbox')->where('company_id', $companyId))
            ->whereIn('type', ['إيداع مباشر', 'سحب مباشر'])->latest()->get();

        return view('cashbox.index', [
            'banksOnly' => $banksOnly,
            'cashboxes' => $cashboxes,
            'visibleCashboxes' => $visibleCashboxes,
            'selectedId' => $selectedId,
            'customers' => Customer::where('company_id', $companyId)->orderBy('name')->get(),
            'balance' => $visibleCashboxes->sum('balance'),
            'receipts' => $receipts,
            'payments' => $payments,
            'cashboxLogs' => $cashboxLogs,
            'totalReceipts' => $receipts->sum('amount') + $cashboxLogs->where('type', 'إيداع مباشر')->sum('amount'),
            'totalPayments' => $payments->sum('amount') + $cashboxLogs->where('type', 'سحب مباشر')->sum('amount'),
        ]);
    }

    public function statement(Request $request, Cashbox $cashbox, CashboxStatementService $statements)
    {
        $this->ensureOwned($cashbox);
        if ($request->routeIs('banks.*')) {
            abort_unless($cashbox->account_type === 'bank', 404);
        }
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
        ]);
        $data = $statements->calculate($cashbox, $filters['from'] ?? null, $filters['to'] ?? null);
        $data['statementRoute'] = $cashbox->account_type === 'bank' ? 'banks.statement' : 'cashbox.statement';

        return view($request->routeIs('*.print') ? 'cashbox.statement-print' : 'cashbox.statement', $data);
    }

    public function storeBank(Request $request)
    {
        $request->merge(['account_type' => 'bank', 'bank_name' => $request->input('name')]);

        return $this->store($request);
    }

    public function updateBank(Request $request, Cashbox $cashbox)
    {
        $this->ensureOwned($cashbox);
        abort_unless($cashbox->account_type === 'bank', 404);
        $request->merge(['account_type' => 'bank', 'bank_name' => $request->input('name')]);

        return $this->update($request, $cashbox);
    }

    public function bankTransaction(Request $request, Cashbox $cashbox)
    {
        $this->ensureOwned($cashbox);
        abort_unless($cashbox->account_type === 'bank', 404);

        return $this->transaction($request, $cashbox);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->cashboxRules(true));
        $data['balance'] ??= 0;
        $customerIds = $this->ownedCustomerIds($data['customer_ids'] ?? []);
        unset($data['customer_ids']);
        $data['account_type'] ??= 'cash';
        if ($data['account_type'] === 'cash') {
            $data['bank_name'] = null;
            $data['account_number'] = null;
        }
        $cashbox = Cashbox::create($data + ['company_id' => auth()->user()->company_id, 'is_active' => true]);
        $cashbox->customers()->sync($customerIds);

        return back()->with('success', __('messages.account_created'));
    }

    public function update(Request $request, Cashbox $cashbox)
    {
        $this->ensureOwned($cashbox);
        $data = $request->validate($this->cashboxRules(false));
        $customerIds = $this->ownedCustomerIds($data['customer_ids'] ?? []);
        unset($data['customer_ids'], $data['balance']);
        $data['account_type'] ??= $cashbox->account_type ?? 'cash';
        if ($data['account_type'] === 'cash') {
            $data['bank_name'] = null;
            $data['account_number'] = null;
        }
        $cashbox->update($data + ['is_active' => $request->boolean('is_active')]);
        $cashbox->customers()->sync($customerIds);

        return back()->with('success', __('messages.account_updated'));
    }

    public function destroy(Cashbox $cashbox)
    {
        $this->ensureOwned($cashbox);
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
            'customer_id' => ['nullable', 'integer'],
        ]);

        $customer = isset($data['customer_id'])
            ? Customer::whereKey($data['customer_id'])->where('company_id', auth()->user()->company_id)->firstOrFail()
            : null;
        if ($customer && ! $cashbox->customers()->whereKey($customer->id)->exists()) {
            throw ValidationException::withMessages(['customer_id' => 'الزبون غير مرتبط بهذا الحساب.']);
        }

        DB::transaction(function () use ($cashbox, $data, $customer): void {
            $lockedCashbox = Cashbox::whereKey($cashbox->id)
                ->where('company_id', auth()->user()->company_id)
                ->lockForUpdate()->firstOrFail();
            abort_unless($lockedCashbox->is_active, 422, __('messages.account_inactive'));
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

            $voucher = null;
            if ($customer) {
                $voucherData = [
                    'company_id' => $lockedCashbox->company_id,
                    'customer_id' => $customer->id,
                    'cashbox_id' => $lockedCashbox->id,
                    'amount' => $amount,
                    'notes' => $data['notes'] ?? null,
                ];
                $voucherType = $data['type'] === 'deposit' ? 'receipt' : 'payment';
                $reference = app(VoucherNumberService::class)
                    ->next($lockedCashbox->company_id, $voucherType, now()->year);
                $voucher = $data['type'] === 'deposit'
                    ? Receipt::create($voucherData + ['receipt_no' => $reference, 'receipt_date' => now()->toDateString()])
                    : Payment::create($voucherData + ['payment_no' => $reference, 'payment_date' => now()->toDateString()]);
            }

            CashboxLog::create([
                'company_id' => $lockedCashbox->company_id,
                'cashbox_id' => $lockedCashbox->id,
                // Linked operations are already included in receipt/payment totals.
                'type' => $customer
                    ? ($data['type'] === 'deposit' ? 'قبض' : 'صرف')
                    : ($data['type'] === 'deposit' ? 'إيداع مباشر' : 'سحب مباشر'),
                'reference_no' => $voucher ? ($voucher->receipt_no ?? $voucher->payment_no) : 'TXN-'.Str::ulid(),
                'person_name' => $customer?->name ?? auth()->user()->name,
                'amount' => $amount,
                'balance_after' => $lockedCashbox->balance,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return back()->with('success', $data['type'] === 'deposit'
            ? __('messages.deposit_saved')
            : __('messages.withdrawal_saved'));
    }

    private function cashboxRules(bool $creating): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'not_in:الصندوق الرئيسي'],
            'account_type' => ['nullable', 'in:cash,bank'],
            'bank_name' => ['nullable', 'required_if:account_type,bank', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'balance' => $creating ? ['nullable', 'numeric', 'min:0'] : ['nullable'],
            'is_active' => ['nullable', 'boolean'],
            'customer_ids' => ['nullable', 'array'],
            'customer_ids.*' => ['integer'],
        ];
    }

    private function ownedCustomerIds(array $ids): array
    {
        return Customer::where('company_id', auth()->user()->company_id)
            ->whereIn('id', $ids)->pluck('id')->all();
    }

    private function ensureOwned(Cashbox $cashbox): void
    {
        abort_unless($cashbox->company_id === auth()->user()->company_id, 404);
        abort_if($cashbox->is_system_legacy, 404);
    }
}
