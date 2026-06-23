<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\Receipt;
use App\Models\Payment;

class CashboxController extends Controller
{
    public function index()
    {
        $cashbox = Cashbox::first();

        return view('cashbox.index', [
            'balance' => $cashbox->balance ?? 0,
            'receipts' => Receipt::latest()->get(),
            'payments' => Payment::latest()->get(),
            'totalReceipts' => Receipt::sum('amount'),
            'totalPayments' => Payment::sum('amount'),
        ]);
    }
}