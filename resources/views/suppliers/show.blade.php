@extends('layouts.app')

@section('content')

<div class="topbar">
    <div style="display:flex;justify-content:space-between;align-items:center;">

        <div>
            <h1 class="page-title">{{ $supplier->name }}</h1>
            <p style="color:#8A8178">
                كشف حساب المورد
            </p>
        </div>

        <a href="/suppliers/{{ $supplier->id }}/print"
           target="_blank"
           class="btn">
           طباعة كشف الحساب
        </a>

    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px">

    <div class="card">
        <h3>عدد سندات الصرف</h3>
        <h1>{{ $paymentsCount }}</h1>
    </div>

    <div class="card">
        <h3>إجمالي المصروفات</h3>
        <h1 style="color:#B91C1C">
            {{ number_format($totalPayments,2) }}
        </h1>
    </div>

    <div class="card">
        <h3>رقم الهاتف</h3>
        <h1>{{ $supplier->phone ?? '-' }}</h1>
    </div>

</div>

<div class="card">

    <h2>سندات الصرف</h2>

    <table>
        <thead>
            <tr>
                <th>رقم الوصل</th>
                <th>التاريخ</th>
                <th>المبلغ</th>
                <th>الملاحظات</th>
            </tr>
        </thead>

        <tbody>

        @foreach($payments as $payment)

            <tr>
                <td>{{ $payment->payment_no }}</td>

                <td>{{ $payment->payment_date }}</td>

                <td style="color:#B91C1C;font-weight:700">
                    {{ number_format($payment->amount,2) }}
                </td>

                <td>{{ $payment->notes ?? '-' }}</td>
            </tr>

        @endforeach

        </tbody>

    </table>

</div>

@endsection