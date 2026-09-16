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
}
