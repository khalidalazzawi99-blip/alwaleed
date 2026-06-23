<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Receipt;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->get();

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        return redirect('/customers');
    }

    public function show(Customer $customer)
    {
        $receipts = Receipt::where('customer_id', $customer->id)
            ->latest()
            ->get();

        return view('customers.show', [
            'customer' => $customer,
            'receipts' => $receipts,
            'totalReceipts' => $receipts->sum('amount'),
            'receiptsCount' => $receipts->count(),
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect('/customers');
    }
    public function print(Customer $customer)
{
    $receipts = \App\Models\Receipt::where('customer_id', $customer->id)
        ->latest()
        ->get();

    return view('customers.print', [
        'customer' => $customer,
        'receipts' => $receipts,
        'totalReceipts' => $receipts->sum('amount'),
        'receiptsCount' => $receipts->count(),
    ]);
}
}