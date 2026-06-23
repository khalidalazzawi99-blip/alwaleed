@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">🔴 سند صرف</h1>
    <p style="color:#64748B">تسجيل المبالغ الخارجة من الصندوق</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1.3fr;gap:22px">

<div class="card">
<h2>سند جديد</h2>

<form method="POST" action="/payments">
@csrf

<label style="font-weight:700;display:block;margin-bottom:20px;">رقم الوصل</label>
<input type="text" value="{{ $nextPaymentNo }}" readonly>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">التاريخ</label>
<input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">المورد</label>
<select name="supplier_id" required>
<option value="">اختر المورد</option>
@foreach($suppliers as $supplier)
<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
@endforeach
</select>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">المبلغ</label>
<input type="number" name="amount" step="0.01" placeholder="250000" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">الملاحظات</label>
<textarea name="notes" rows="4" placeholder="اكتب ملاحظات السند"></textarea>

<br><br>

<button type="submit" class="danger" style="width:100%;padding:16px">
حفظ سند الصرف
</button>

</form>
</div>

<div class="card">
<h2>سندات الصرف</h2>
<input
    type="text"
    id="paymentSearch"
    placeholder="بحث في سندات الصرف..."
    style="margin-bottom:20px">
<table>
<thead>
<tr>
<th>رقم الوصل</th>
<th>التاريخ</th>
<th>المورد</th>
<th>المبلغ</th>
<th>الملاحظات</th>
<th>طباعة</th>
</tr>
</thead>

<tbody>
@foreach($payments as $payment)
<tr>
<td>{{ $payment->payment_no }}</td>
<td>{{ $payment->payment_date }}</td>
<td>{{ $payment->supplier->name ?? '-' }}</td>
<td style="color:#DC2626;font-weight:800">{{ number_format($payment->amount,2) }}</td>
<td>{{ $payment->notes }}</td>
<td>
    <div style="display:flex;gap:8px;align-items:center;justify-content:center;flex-wrap:nowrap;">

        <a href="/payments/{{ $payment->id }}/edit"
           class="btn"
           style="padding:10px 16px;white-space:nowrap;">
            تعديل
        </a>

        <a href="/payments/{{ $payment->id }}/print"
           class="btn"
           target="_blank"
           style="padding:10px 16px;white-space:nowrap;">
            طباعة
        </a>

        <form action="/payments/{{ $payment->id }}"
              method="POST"
              style="display:inline;margin:0;">

            @csrf
            @method('DELETE')

            <button type="submit"
                    class="danger"
                    style="font-family:'Tajawal',sans-serif;padding:10px 16px;border-radius:14px;font-weight:700;background:#DC2626;color:white;border:none;white-space:nowrap;"
                    onclick="return confirm('هل أنت متأكد من حذف السند؟')">
                حذف
            </button>

        </form>

    </div>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>

</div>
<script>
document.getElementById('paymentSearch').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');

    rows.forEach(function (row) {
        row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
});
</script>
@endsection