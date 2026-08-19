@extends('layouts.app')

@section('content')

<div class="topbar">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1 class="page-title">{{ __('التقارير') }}</h1>
            <p style="color:#8A8178">{{ __('مركز مراقبة كامل لحركة النظام') }}</p>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="/reports/print?{{ http_build_query(array_filter(['from'=>$from,'to'=>$to])) }}" target="_blank" class="btn">{{ __('messages.print') }}</a>
            <a href="/reports/pdf?{{ http_build_query(array_filter(['from'=>$from,'to'=>$to])) }}" class="btn">{{ __('messages.export_pdf') }}</a>
            <a href="/reports/excel?{{ http_build_query(array_filter(['from'=>$from,'to'=>$to])) }}" class="btn">{{ __('messages.export_excel') }}</a>
        </div>
    </div>
</div>

<div class="card">
    <form method="GET" action="/reports"
          style="display:grid;grid-template-columns:1fr 1fr auto;gap:15px;align-items:end">

        <div>
            <label>{{ __('من تاريخ') }}</label>
            <input type="date" name="from" value="{{ $from }}">
        </div>

        <div>
            <label>{{ __('إلى تاريخ') }}</label>
            <input type="date" name="to" value="{{ $to }}">
        </div>

        <button type="submit">{{ __('عرض التقرير') }}</button>
    </form>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px">
    <div class="card">
        <h3>{{ __('الرصيد الحالي') }}</h3>
        <h1 style="color:#CDBA9E">{{ number_format($balance,2) }}</h1>
    </div>

    <div class="card">
        <h3>{{ __('إجمالي القبض') }}</h3>
        <h1 style="color:#15803D">{{ number_format($totalReceipts,2) }}</h1>
    </div>

    <div class="card">
        <h3>{{ __('إجمالي الصرف') }}</h3>
        <h1 style="color:#B91C1C">{{ number_format($totalPayments,2) }}</h1>
    </div>

    <div class="card">
        <h3>{{ __('صافي الحركة') }}</h3>
        <h1 style="color:#CDBA9E">{{ number_format($totalReceipts - $totalPayments,2) }}</h1>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-top:20px">
    <div class="card">
        <h3>{{ __('الزبائن') }}</h3>
        <h1>{{ $customers }}</h1>
    </div>

    <div class="card">
        <h3>{{ __('الموردين') }}</h3>
        <h1>{{ $suppliers }}</h1>
    </div>

    <div class="card">
        <h3>{{ __('سندات القبض') }}</h3>
        <h1>{{ $receiptsCount }}</h1>
    </div>

    <div class="card">
        <h3>{{ __('سندات الصرف') }}</h3>
        <h1>{{ $paymentsCount }}</h1>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-top:22px">
    <div class="card">
        <h2>{{ __('آخر سندات القبض') }}</h2>

        <table>
            <thead>
                <tr>
                    <th>{{ __('رقم الوصل') }}</th>
                    <th>{{ __('الزبون') }}</th>
                    <th>{{ __('المبلغ') }}</th>
                </tr>
            </thead>

            <tbody>
            @foreach($receipts->take(10) as $receipt)
                <tr>
                    <td>{{ $receipt->receipt_no }}</td>
                    <td>{{ $receipt->party?->name ?? '-' }}</td>
                    <td style="color:#15803D;font-weight:700">
                        {{ number_format($receipt->amount,2) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>{{ __('آخر سندات الصرف') }}</h2>

        <table>
            <thead>
                <tr>
                    <th>{{ __('رقم الوصل') }}</th>
                    <th>{{ __('المورد') }}</th>
                    <th>{{ __('المبلغ') }}</th>
                </tr>
            </thead>

            <tbody>
            @foreach($payments->take(10) as $payment)
                <tr>
                    <td>{{ $payment->payment_no }}</td>
                    <td>{{ $payment->party?->name ?? '-' }}</td>
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
    <h2>{{ __('ملخص حركة الصندوق للفترة المحددة') }}</h2>

    <table>
        <thead>
            <tr>
                <th>{{ __('البيان') }}</th>
                <th>{{ __('المبلغ') }}</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>{{ __('إجمالي الداخل') }}</td>
                <td style="color:#15803D;font-weight:700">{{ number_format($totalReceipts,2) }}</td>
            </tr>

            <tr>
                <td>{{ __('إجمالي الخارج') }}</td>
                <td style="color:#B91C1C;font-weight:700">{{ number_format($totalPayments,2) }}</td>
            </tr>

            <tr>
                <td>{{ __('صافي الحركة') }}</td>
                <td style="color:#CDBA9E;font-weight:700">{{ number_format($totalReceipts - $totalPayments,2) }}</td>
            </tr>

            <tr>
                <td>{{ __('الرصيد الحالي') }}</td>
                <td style="font-weight:700">{{ number_format($balance,2) }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:22px">
    <h2>{{ __('حركة الصندوق اليومية') }}</h2>

    <table>
        <thead>
            <tr>
                <th>{{ __('التاريخ') }}</th>
                <th>{{ __('الداخل') }}</th>
                <th>{{ __('الخارج') }}</th>
                <th>{{ __('الصافي') }}</th>
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
