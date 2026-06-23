<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Payment;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        Supplier::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        return redirect('/suppliers');
    }

    public function show(Supplier $supplier)
    {
        $payments = Payment::where('supplier_id', $supplier->id)
            ->latest()
            ->get();

        return view('suppliers.show', [
            'supplier' => $supplier,
            'payments' => $payments,
            'totalPayments' => $payments->sum('amount'),
            'paymentsCount' => $payments->count(),
        ]);
    }
    public function print(Supplier $supplier)
{
    $payments = \App\Models\Payment::where('supplier_id', $supplier->id)
        ->latest()
        ->get();

    return view('suppliers.print', [
        'supplier' => $supplier,
        'payments' => $payments,
        'totalPayments' => $payments->sum('amount'),
        'paymentsCount' => $payments->count(),
    ]);
}
}