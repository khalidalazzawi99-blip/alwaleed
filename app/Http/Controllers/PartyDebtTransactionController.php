<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PartyDebtTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PartyDebtTransactionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'party_type' => ['required', 'in:customer,supplier'],
            'party_id' => ['required', 'integer'],
            'type' => ['required', 'in:borrowing,debt_payment'],
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $companyId = auth()->user()->company_id;
        $model = $data['party_type'] === 'customer' ? Customer::class : Supplier::class;
        $party = $model::whereKey($data['party_id'])->where('company_id', $companyId)->firstOrFail();

        PartyDebtTransaction::create([
            'company_id' => $companyId,
            'customer_id' => $data['party_type'] === 'customer' ? $party->id : null,
            'supplier_id' => $data['party_type'] === 'supplier' ? $party->id : null,
            'type' => $data['type'],
            'transaction_date' => $data['transaction_date'],
            'amount' => $data['amount'],
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect('/'.$data['party_type'].'s/'.$party->id)
            ->with('success', $data['type'] === 'borrowing' ? 'تم تسجيل الاستدانة بنجاح' : 'تم تسجيل سداد الدين بنجاح');
    }

    public function edit(PartyDebtTransaction $transaction)
    {
        $this->ensureCompany($transaction);

        return view('party_debt_transactions.edit', [
            'transaction' => $transaction,
            'party' => $transaction->customer ?: $transaction->supplier,
            'partyType' => $transaction->customer_id ? 'customer' : 'supplier',
        ]);
    }

    public function update(Request $request, PartyDebtTransaction $transaction)
    {
        $this->ensureCompany($transaction);
        $transaction->update($request->validate([
            'type' => ['required', 'in:borrowing,debt_payment'],
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]));

        return redirect($this->partyUrl($transaction))->with('success', 'تم تعديل الحركة وإعادة احتساب الرصيد بنجاح.');
    }

    public function destroy(PartyDebtTransaction $transaction)
    {
        $this->ensureCompany($transaction);
        $redirect = $this->partyUrl($transaction);
        $transaction->delete();

        return redirect($redirect)->with('success', 'تم حذف الحركة وإعادة احتساب الرصيد بنجاح.');
    }

    private function ensureCompany(PartyDebtTransaction $transaction): void
    {
        abort_unless((int) $transaction->company_id === (int) auth()->user()->company_id, 403);
    }

    private function partyUrl(PartyDebtTransaction $transaction): string
    {
        return $transaction->customer_id
            ? '/customers/'.$transaction->customer_id
            : '/suppliers/'.$transaction->supplier_id;
    }
}
