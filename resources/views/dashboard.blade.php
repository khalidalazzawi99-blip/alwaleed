@extends('layouts.app')

@section('content')
<div style="
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
">

<div>
<h1 class="page-title">Al Waleed</h1>
<p style="color:#8A8178">
Accounts & Business Management System
</p>
</div>

<div style="
background:#F5F1EB;
padding:12px 20px;
border-radius:14px;
font-weight:700;
color:#A68A64;
">
نظام إدارة الحسابات
</div>

</div>
<div class="topbar">
    <div style="display:flex;justify-content:space-between;align-items:center">
        <div>
            <h1 class="page-title">لوحة التحكم</h1>
            <p style="color:#8A8178;margin:8px 0 0">
                نظرة سريعة على حسابات ونشاط النظام
            </p>
        </div>

        <div style="display:flex;gap:10px">
            <a href="/receipts" class="btn">سند قبض</a>
            <a href="/payments" class="btn">سند صرف</a>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:18px">

    <div class="card">
        <h3>الرصيد الحالي</h3>
        <h1 style="color:#CDBA9E">{{ number_format($balance,2) }}</h1>
        <p>IQD</p>
    </div>

    <div class="card">
        <h3>إجمالي القبض</h3>
        <h1 style="color:#15803D">{{ number_format($totalReceipts,2) }}</h1>
        <p>{{ $receiptsCount }} سند</p>
    </div>

    <div class="card">
        <h3>إجمالي الصرف</h3>
        <h1 style="color:#B91C1C">{{ number_format($totalPayments,2) }}</h1>
        <p>{{ $paymentsCount }} سند</p>
    </div>

    <div class="card">
        <h3>الزبائن</h3>
        <h1>{{ $customers }}</h1>
        <p>عميل مسجل</p>
    </div>

    <div class="card">
        <h3>الموردين</h3>
        <h1>{{ $suppliers }}</h1>
        <p>مورد مسجل</p>
    </div>

</div>

<div style="display:grid;grid-template-columns:1.2fr .8fr;gap:22px;margin-top:22px">

    <div class="card">
        <h2>الإجراءات السريعة</h2>

        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:15px;margin-top:20px">
            <a href="/customers" class="btn">إدارة الزبائن</a>
            <a href="/suppliers" class="btn">إدارة الموردين</a>
            <a href="/receipts" class="btn" style="background:#15803D">إضافة سند قبض</a>
            <a href="/payments" class="btn" style="background:#B91C1C">إضافة سند صرف</a>
        </div>
    </div>

    <div class="card">
        <h2>ملخص مالي</h2>

        <div style="margin-top:20px;line-height:2.2">
            <div>إجمالي الداخل: <strong style="color:#15803D">{{ number_format($totalReceipts,2) }} IQD</strong></div>
            <div>إجمالي الخارج: <strong style="color:#B91C1C">{{ number_format($totalPayments,2) }} IQD</strong></div>
            <div>الرصيد الحالي: <strong style="color:#CDBA9E">{{ number_format($balance,2) }} IQD</strong></div>
        </div>
    </div>

</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-top:22px">

    <div class="card">
        <h2>آخر سندات القبض</h2>

        <table>
            <thead>
                <tr>
                    <th>رقم الوصل</th>
                    <th>المبلغ</th>
                </tr>
            </thead>

            <tbody>
            @foreach($latestReceipts as $receipt)
                <tr>
                    <td>{{ $receipt->receipt_no }}</td>
                    <td style="color:#15803D">
                        {{ number_format($receipt->amount,2) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
            @if($balance < 100000)
<div class="card" style="background:#FEF2F2;border-color:#FCA5A5">
    <h2 style="color:#B91C1C;margin:0">
        تنبيه: رصيد الصندوق منخفض
    </h2>
    <p style="color:#7F1D1D">
        الرصيد الحالي أقل من 100,000 IQD
    </p>
</div>
@endif

<div class="card">
    <h2>صافي الحركة المالية</h2>

    <h1 style="color:#CDBA9E">
        {{ number_format($totalReceipts - $totalPayments,2) }} IQD
    </h1>

    <p style="color:#8A8178">
        الداخل ناقص الخارج
    </p>
</div>
        </table>
    </div>

    <div class="card">
        <h2>آخر سندات الصرف</h2>

        <table>
            <thead>
                <tr>
                    <th>رقم الوصل</th>
                    <th>المبلغ</th>
                </tr>
            </thead>

            <tbody>
            @foreach($latestPayments as $payment)
                <tr>
                    <td>{{ $payment->payment_no }}</td>
                    <td style="color:#B91C1C">
                        {{ number_format($payment->amount,2) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection