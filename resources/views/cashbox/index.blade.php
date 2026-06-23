@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">💰 الصندوق</h1>
    <p style="color:#64748B">متابعة رصيد الصندوق وحركة القبض والصرف</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px">

    <div class="card">
        <h3>الرصيد الحالي</h3>
        <h1 style="color:#2563EB">
            {{ number_format($balance,2) }} IQD
        </h1>
    </div>

    <div class="card">
        <h3>إجمالي الداخل</h3>
        <h1 style="color:#16A34A">
            {{ number_format($totalReceipts,2) }} IQD
        </h1>
    </div>

    <div class="card">
        <h3>إجمالي الخارج</h3>
        <h1 style="color:#DC2626">
            {{ number_format($totalPayments,2) }} IQD
        </h1>
    </div>

</div>

<div class="card">

    <h2>حركة الصندوق</h2>

    <table>

        <thead>
            <tr>
                <th>النوع</th>
                <th>رقم الوصل</th>
                <th>الاسم</th>
                <th>المبلغ</th>
                <th>التاريخ</th>
                <th>الملاحظات</th>
            </tr>
        </thead>

        <tbody>

        @foreach($receipts as $receipt)

            <tr>
                <td style="color:#16A34A;font-weight:800">
                    قبض
                </td>

                <td>{{ $receipt->receipt_no }}</td>

                <td>{{ $receipt->customer->name ?? '-' }}</td>

                <td style="color:#16A34A;font-weight:800">
                    +{{ number_format($receipt->amount,2) }}
                </td>

                <td>{{ $receipt->receipt_date }}</td>

                <td>{{ $receipt->notes }}</td>
            </tr>

        @endforeach

        @foreach($payments as $payment)

            <tr>
                <td style="color:#DC2626;font-weight:800">
                    صرف
                </td>

                <td>{{ $payment->payment_no }}</td>

                <td>{{ $payment->supplier->name ?? '-' }}</td>

                <td style="color:#DC2626;font-weight:800">
                    -{{ number_format($payment->amount,2) }}
                </td>

                <td>{{ $payment->payment_date }}</td>

                <td>{{ $payment->notes }}</td>
            </tr>

        @endforeach

        </tbody>

    </table>

</div>

@endsection