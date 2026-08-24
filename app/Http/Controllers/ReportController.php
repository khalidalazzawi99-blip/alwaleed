<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Receipt;
use App\Models\Payment;
use App\Models\Cashbox;
use App\Exports\ArrayExport;
use App\Services\DocumentExportService;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $from = $request->from;
        $to = $request->to;

        $receiptsQuery = Receipt::where('company_id', $companyId);
        $paymentsQuery = Payment::where('company_id', $companyId);

        if ($from && $to) {
            $receiptsQuery->whereBetween('receipt_date', [$from, $to]);
            $paymentsQuery->whereBetween('payment_date', [$from, $to]);
        }

        $receipts = $receiptsQuery
            ->latest('receipt_date')
            ->get();

        $payments = $paymentsQuery
            ->latest('payment_date')
            ->get();

        $dailyReceipts = $receipts
            ->groupBy('receipt_date')
            ->map(fn ($items) => $items->sum('amount'));

        $dailyPayments = $payments
            ->groupBy('payment_date')
            ->map(fn ($items) => $items->sum('amount'));

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

            'customers' => Customer::where('company_id', $companyId)->count(),

            'suppliers' => Supplier::where('company_id', $companyId)->count(),

            'receiptsCount' => $receipts->count(),

            'paymentsCount' => $payments->count(),

            'receipts' => $receipts,

            'payments' => $payments,

            'totalReceipts' => $receipts->sum('amount'),

            'totalPayments' => $payments->sum('amount'),

            'balance' => Cashbox::where('company_id', $companyId)->sum('balance'),

            'from' => $from,

            'to' => $to,
        ]);
    }

    public function print(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $from = $request->from;
        $to = $request->to;

        $receiptsQuery = Receipt::where('company_id', $companyId);
        $paymentsQuery = Payment::where('company_id', $companyId);

        if ($from && $to) {
            $receiptsQuery->whereBetween('receipt_date', [$from, $to]);
            $paymentsQuery->whereBetween('payment_date', [$from, $to]);
        }

        $receipts = $receiptsQuery
            ->latest('receipt_date')
            ->get();

        $payments = $paymentsQuery
            ->latest('payment_date')
            ->get();

        $dailyReceipts = $receipts
            ->groupBy('receipt_date')
            ->map(fn ($items) => $items->sum('amount'));

        $dailyPayments = $payments
            ->groupBy('payment_date')
            ->map(fn ($items) => $items->sum('amount'));

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

            'balance' => Cashbox::where('company_id', $companyId)->sum('balance'),

            'from' => $from,

            'to' => $to,
        ]);
    }

    public function pdf(Request $request, DocumentExportService $exports)
    {
        return $exports->pdf('reports.print', $this->exportData($request), 'financial-report-'.now()->format('Ymd').'.pdf');
    }

    public function excel(Request $request)
    {
        $data = $this->exportData($request);
        $rows = [];

        foreach ($data['receipts'] as $receipt) {
            $rows[] = [
                $receipt->receipt_date, __('قبض'), $receipt->receipt_no,
                $receipt->party?->name, $receipt->amount, 0,
                $receipt->notes,
            ];
        }

        foreach ($data['payments'] as $payment) {
            $rows[] = [
                $payment->payment_date, __('صرف'), $payment->payment_no,
                $payment->party?->name, 0, $payment->amount,
                $payment->notes,
            ];
        }

        usort($rows, fn ($left, $right) => strcmp((string) $left[0], (string) $right[0]));
        $rows[] = ['', __('messages.total'), '', '', $data['totalReceipts'], $data['totalPayments'], ''];

        return Excel::download(new ArrayExport([
            __('messages.date'), __('messages.movement_type'), __('messages.reference'),
            __('messages.name'), __('messages.received'), __('messages.paid'), __('messages.notes'),
        ], $rows, 'التقرير المالي', auth()->user()->company_id), 'financial-report-'.now()->format('Ymd').'.xlsx');
    }

    private function exportData(Request $request): array
    {
        $companyId = auth()->user()->company_id;
        $from = $request->from;
        $to = $request->to;
        $receipts = Receipt::with(['customer', 'supplier'])->where('company_id', $companyId)
            ->when($from, fn ($query) => $query->whereDate('receipt_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('receipt_date', '<=', $to))
            ->latest('receipt_date')->get();
        $payments = Payment::with(['customer', 'supplier'])->where('company_id', $companyId)
            ->when($from, fn ($query) => $query->whereDate('payment_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('payment_date', '<=', $to))
            ->latest('payment_date')->get();
        $dailyReceipts = $receipts->groupBy('receipt_date')->map->sum('amount');
        $dailyPayments = $payments->groupBy('payment_date')->map->sum('amount');
        $dates = $dailyReceipts->keys()->merge($dailyPayments->keys())->unique()->sort()->values();
        $cashMovement = $dates->map(fn ($date) => [
            'date' => $date,
            'in' => $dailyReceipts[$date] ?? 0,
            'out' => $dailyPayments[$date] ?? 0,
            'net' => ($dailyReceipts[$date] ?? 0) - ($dailyPayments[$date] ?? 0),
        ])->all();

        return [
            'cashMovement' => $cashMovement,
            'receipts' => $receipts,
            'payments' => $payments,
            'totalReceipts' => $receipts->sum('amount'),
            'totalPayments' => $payments->sum('amount'),
            'balance' => Cashbox::where('company_id', $companyId)->sum('balance'),
            'from' => $from,
            'to' => $to,
        ];
    }
}
