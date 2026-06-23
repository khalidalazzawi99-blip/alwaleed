<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Cashbox;
use App\Models\Receipt;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $cashbox = Cashbox::first();

        return view('dashboard', [
            'customers' => Customer::count(),
            'suppliers' => Supplier::count(),
            'balance' => $cashbox->balance ?? 0,
            'totalReceipts' => Receipt::sum('amount'),
            'totalPayments' => Payment::sum('amount'),
            'receiptsCount' => Receipt::count(),
            'paymentsCount' => Payment::count(),
            'latestReceipts' => Receipt::latest()->take(5)->get(),
'latestPayments' => Payment::latest()->take(5)->get(),
        ]);
    }
}