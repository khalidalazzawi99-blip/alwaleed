<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Receipt;
use App\Models\Payment;
use App\Models\Cashbox;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        $receiptsQuery = Receipt::query();
        $paymentsQuery = Payment::query();

        if ($from && $to) {
            $receiptsQuery->whereBetween('receipt_date', [$from, $to]);
            $paymentsQuery->whereBetween('payment_date', [$from, $to]);
        }

        $receipts = $receiptsQuery->latest()->get();
        $payments = $paymentsQuery->latest()->get();

        $dailyReceipts = $receipts
            ->groupBy('receipt_date')
            ->map(fn($items) => $items->sum('amount'));

        $dailyPayments = $payments
            ->groupBy('payment_date')
            ->map(fn($items) => $items->sum('amount'));

        $dates = collect(
            array_unique(
                array_merge(
                    $dailyReceipts->keys()->toArray(),
                    $dailyPayments->keys()->toArray()
                )
            )
        )->sort()->values();

        $cashMovement = [];

        foreach ($dates as $date) {
            $in = $dailyReceipts[$date] ?? 0;
            $out = $dailyPayments[$date] ?? 0;

            $cashMovement[] = [
                'date' => $date,
                'in' => $in,
                'out' => $out,
                'net' => $in - $out,
            ];
        }

        return view('reports.index', [
            'cashMovement' => $cashMovement,
            'customers' => Customer::count(),
            'suppliers' => Supplier::count(),
            'receiptsCount' => $receipts->count(),
            'paymentsCount' => $payments->count(),
            'receipts' => $receipts,
            'payments' => $payments,
            'totalReceipts' => $receipts->sum('amount'),
            'totalPayments' => $payments->sum('amount'),
            'balance' => Cashbox::first()->balance ?? 0,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function print(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        $receiptsQuery = Receipt::query();
        $paymentsQuery = Payment::query();

        if ($from && $to) {
            $receiptsQuery->whereBetween('receipt_date', [$from, $to]);
            $paymentsQuery->whereBetween('payment_date', [$from, $to]);
        }

        $receipts = $receiptsQuery->latest()->get();
        $payments = $paymentsQuery->latest()->get();
$dailyReceipts = $receipts
    ->groupBy('receipt_date')
    ->map(fn($items) => $items->sum('amount'));

$dailyPayments = $payments
    ->groupBy('payment_date')
    ->map(fn($items) => $items->sum('amount'));

$dates = collect(
    array_unique(
        array_merge(
            $dailyReceipts->keys()->toArray(),
            $dailyPayments->keys()->toArray()
        )
    )
)->sort()->values();

$cashMovement = [];

foreach ($dates as $date) {
    $in = $dailyReceipts[$date] ?? 0;
    $out = $dailyPayments[$date] ?? 0;

    $cashMovement[] = [
        'date' => $date,
        'in' => $in,
        'out' => $out,
        'net' => $in - $out,
    ];
}
        return view('reports.print', [
            'cashMovement' => $cashMovement,
            'receipts' => $receipts,
            'payments' => $payments,
            'totalReceipts' => $receipts->sum('amount'),
            'totalPayments' => $payments->sum('amount'),
            'balance' => Cashbox::first()->balance ?? 0,
            'from' => $from,
            'to' => $to,
        ]);
    }
}