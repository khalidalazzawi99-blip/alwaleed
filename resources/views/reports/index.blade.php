@extends('layouts.app')

@section('content')

<div class="topbar">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1 class="page-title">التقارير</h1>
            <p style="color:#8A8178">مركز مراقبة كامل لحركة النظام</p>
        </div>

        <a href="/reports/print?from={{ $from }}&to={{ $to }}"
   target="_blank"
   class="btn">
   طباعة التقرير
</a>
    </div>
</div>

<div class="card">
    <form method="GET" action="/reports"
          style="display:grid;grid-template-columns:1fr 1fr auto;gap:15px;align-items:end">

        <div>
            <label>من تاريخ</label>
            <input type="date" name="from" value="{{ $from }}">
        </div>

        <div>
            <label>إلى تاريخ</label>
            <input type="date" name="to" value="{{ $to }}">
        </div>

        <button type="submit">عرض التقرير</button>
    </form>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px">
    <div class="card">
        <h3>الرصيد الحالي</h3>
        <h1 style="color:#CDBA9E">{{ number_format($balance,2) }}</h1>
    </div>

    <div class="card">
        <h3>إجمالي القبض</h3>
        <h1 style="color:#15803D">{{ number_format($totalReceipts,2) }}</h1>
    </div>

    <div class="card">
        <h3>إجمالي الصرف</h3>
        <h1 style="color:#B91C1C">{{ number_format($totalPayments,2) }}</h1>
    </div>

    <div class="card">
        <h3>صافي الحركة</h3>
        <h1 style="color:#CDBA9E">{{ number_format($totalReceipts - $totalPayments,2) }}</h1>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-top:20px">
    <div class="card">
        <h3>الزبائن</h3>
        <h1>{{ $customers }}</h1>
    </div>

    <div class="card">
        <h3>الموردين</h3>
        <h1>{{ $suppliers }}</h1>
    </div>

    <div class="card">
        <h3>سندات القبض</h3>
        <h1>{{ $receiptsCount }}</h1>
    </div>

    <div class="card">
        <h3>سندات الصرف</h3>
        <h1>{{ $paymentsCount }}</h1>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-top:22px">
    <div class="card">
        <h2>آخر سندات القبض</h2>

        <table>
            <thead>
                <tr>
                    <th>رقم الوصل</th>
                    <th>الزبون</th>
                    <th>المبلغ</th>
                </tr>
            </thead>

            <tbody>
            @foreach($receipts->take(10) as $receipt)
                <tr>
                    <td>{{ $receipt->receipt_no }}</td>
                    <td>{{ $receipt->customer->name ?? '-' }}</td>
                    <td style="color:#15803D;font-weight:700">
                        {{ number_format($receipt->amount,2) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>آخر سندات الصرف</h2>

        <table>
            <thead>
                <tr>
                    <th>رقم الوصل</th>
                    <th>المورد</th>
                    <th>المبلغ</th>
                </tr>
            </thead>

            <tbody>
            @foreach($payments->take(10) as $payment)
                <tr>
                    <td>{{ $payment->payment_no }}</td>
                    <td>{{ $payment->supplier->name ?? '-' }}</td>
                    <td style="color:#B91C1C;font-weight:700">
                        {{ number_format($payment->amount,2) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top:22px">
    <h2>ملخص حركة الصندوق للفترة المحددة</h2>

    <table>
        <thead>
            <tr>
                <th>البيان</th>
                <th>المبلغ</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>إجمالي الداخل</td>
                <td style="color:#15803D;font-weight:700">{{ number_format($totalReceipts,2) }}</td>
            </tr>

            <tr>
                <td>إجمالي الخارج</td>
                <td style="color:#B91C1C;font-weight:700">{{ number_format($totalPayments,2) }}</td>
            </tr>

            <tr>
                <td>صافي الحركة</td>
                <td style="color:#CDBA9E;font-weight:700">{{ number_format($totalReceipts - $totalPayments,2) }}</td>
            </tr>

            <tr>
                <td>الرصيد الحالي</td>
                <td style="font-weight:700">{{ number_format($balance,2) }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:22px">
    <h2>حركة الصندوق اليومية</h2>

    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>الداخل</th>
                <th>الخارج</th>
                <th>الصافي</th>
            </tr>
        </thead>

        <tbody>
        @foreach($cashMovement as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td style="color:#15803D">{{ number_format($row['in'],2) }}</td>
                <td style="color:#B91C1C">{{ number_format($row['out'],2) }}</td>
                <td style="font-weight:700">{{ number_format($row['net'],2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection