@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">{{ __('💰 الصندوق') }}</h1>
    <p style="color:#64748B">{{ __('متابعة رصيد الصندوق وحركة القبض والصرف') }}</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px">

    <div class="card">
        <h3>{{ __('الرصيد الحالي') }}</h3>
        <h1 style="color:#2563EB">
            {{ number_format($balance,2) }} {{ $companyCurrency }}
        </h1>
    </div>

    <div class="card">
        <h3>{{ __('إجمالي الداخل') }}</h3>
        <h1 style="color:#16A34A">
            {{ number_format($totalReceipts,2) }} {{ $companyCurrency }}
        </h1>
    </div>

    <div class="card">
        <h3>{{ __('إجمالي الخارج') }}</h3>
        <h1 style="color:#DC2626">
            {{ number_format($totalPayments,2) }} {{ $companyCurrency }}
        </h1>
    </div>

</div>

<div class="card">

    <h2>{{ __('حركة الصندوق') }}</h2>

    <table>

        <thead>
            <tr>
                <th>{{ __('النوع') }}</th>
                <th>{{ __('رقم الوصل') }}</th>
                <th>{{ __('الاسم') }}</th>
                <th>{{ __('المبلغ') }}</th>
                <th>{{ __('التاريخ') }}</th>
                <th>{{ __('الملاحظات') }}</th>
            </tr>
        </thead>

        <tbody>

        @foreach($receipts as $receipt)

            <tr>
                <td style="color:#16A34A;font-weight:800">
                    {{ __('قبض') }}
                </td>

                <td>{{ $receipt->receipt_no }}</td>

                <td>{{ $receipt->party?->name ?? '-' }}</td>

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
                    {{ __('صرف') }}
                </td>

                <td>{{ $payment->payment_no }}</td>

                <td>{{ $payment->party?->name ?? '-' }}</td>

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
