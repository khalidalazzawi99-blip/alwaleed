@extends('layouts.app')

@section('content')

<div class="topbar">
    <div class="topbar">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1 class="page-title">{{ $customer->name }}</h1>
            <p style="color:#8A8178">
                كشف حساب الزبون
            </p>
        </div>

        <a href="/customers/{{ $customer->id }}/print"
           target="_blank"
           class="btn">
           طباعة كشف الحساب
        </a>
    </div>
</div>
    <h1 class="page-title">{{ $customer->name }}</h1>
    <p style="color:#8A8178">
        كشف حساب الزبون
    </p>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px">

    <div class="card">
        <h3>عدد السندات</h3>
        <h1>{{ $receiptsCount }}</h1>
    </div>

    <div class="card">
        <h3>إجمالي المقبوضات</h3>
        <h1 style="color:#15803D">
            {{ number_format($totalReceipts,2) }}
        </h1>
    </div>

    <div class="card">
        <h3>رقم الهاتف</h3>
        <h1>{{ $customer->phone }}</h1>
    </div>

</div>

<div class="card">

    <h2>سندات القبض</h2>

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

        @foreach($receipts as $receipt)

            <tr>
                <td>{{ $receipt->receipt_no }}</td>

                <td>{{ $receipt->receipt_date }}</td>

                <td style="color:#15803D;font-weight:700">
                    {{ number_format($receipt->amount,2) }}
                </td>

                <td>{{ $receipt->notes }}</td>
            </tr>

        @endforeach

        </tbody>

    </table>

</div>

@endsection