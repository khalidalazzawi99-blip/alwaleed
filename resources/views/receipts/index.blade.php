@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">🟢 سند قبض</h1>
    <p style="color:#64748B">تسجيل المبالغ الداخلة إلى الصندوق</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1.3fr;gap:22px">

<div class="card">
<h2>سند جديد</h2>

<form method="POST" action="/receipts">
@csrf

<label style="font-weight:700;display:block;margin-bottom:20px;">رقم الوصل</label>
<input type="text" value="{{ $nextReceiptNo }}" readonly>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">التاريخ</label>
<input type="date" name="receipt_date" value="{{ date('Y-m-d') }}" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">الزبون</label>
<select name="customer_id" required>
<option value="">اختر الزبون</option>
@foreach($customers as $customer)
<option value="{{ $customer->id }}">{{ $customer->name }}</option>
@endforeach
</select>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">المبلغ</label>
<input type="number" name="amount" step="0.01" placeholder="250000" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">الملاحظات</label>
<textarea name="notes" rows="4" placeholder="اكتب ملاحظات السند"></textarea>

<br><br>

<button type="submit" class="success" style="width:100%;padding:16px">
حفظ سند القبض
</button>

</form>
</div>

<div class="card">
<h2>سندات القبض</h2>
<input
    type="text"
    id="receiptSearch"
    placeholder="بحث في سندات القبض..."
    style="margin-bottom:20px">
<table>
<thead>
<tr>
<th>رقم الوصل</th>
<th>التاريخ</th>
<th>الزبون</th>
<th>المبلغ</th>
<th>الملاحظات</th>
<th>طباعة</th>
</tr>
</thead>

<tbody>
@foreach($receipts as $receipt)
<tr>
<td>{{ $receipt->receipt_no }}</td>
<td>{{ $receipt->receipt_date }}</td>
<td>{{ $receipt->customer->name ?? '-' }}</td>
<td style="color:#16A34A;font-weight:800">{{ number_format($receipt->amount,2) }}</td>
<td>{{ $receipt->notes }}</td>
<td>

    <a href="/receipts/{{ $receipt->id }}/edit"
       class="btn"
       style="margin-left:10px;">
       تعديل
    </a>

    <a href="/receipts/{{ $receipt->id }}/print"
       class="btn"
       target="_blank"
       style="margin-left:10px;">
       طباعة
    </a>

    <form action="/receipts/{{ $receipt->id }}"
          method="POST"
          style="display:inline;">

        @csrf
        @method('DELETE')

        <button

    type="submit"
    class="danger"
    style="
    font-family:'Tajawal',sans-serif;
    padding:13px 22px;
    border-radius:14px;
    font-weight:700;
    background:#DC2626;
    color:white;
    border:none;
    cursor:pointer;
    "
    onclick="return confirm('هل أنت متأكد من حذف السند؟')">

    حذف

</button>

    </form>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>

</div>
<script>
document.getElementById('receiptSearch').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');

    rows.forEach(function (row) {
        row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
});
</script>
@endsection