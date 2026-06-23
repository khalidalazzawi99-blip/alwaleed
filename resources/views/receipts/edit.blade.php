@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">تعديل سند قبض</h1>
    <p style="color:#8A8178">تعديل بيانات السند وتحديث رصيد الصندوق تلقائياً</p>
</div>

<div class="card">

<form method="POST" action="/receipts/{{ $receipt->id }}">
@csrf
@method('PUT')

<label style="font-weight:700;display:block;margin-bottom:20px;">رقم الوصل</label>
<input type="text" value="{{ $receipt->receipt_no }}" readonly>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">التاريخ</label>
<input type="date" name="receipt_date" value="{{ $receipt->receipt_date }}" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">الزبون</label>
<select name="customer_id" required>
@foreach($customers as $customer)
<option value="{{ $customer->id }}" {{ $receipt->customer_id == $customer->id ? 'selected' : '' }}>
{{ $customer->name }}
</option>
@endforeach
</select>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">المبلغ</label>
<input type="number" name="amount" step="0.01" value="{{ $receipt->amount }}" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">الملاحظات</label>
<textarea name="notes" rows="4">{{ $receipt->notes }}</textarea>

<br><br>

<button type="submit" class="success" style="padding:16px 35px">
حفظ التعديل
</button>

<a href="/receipts" class="btn" style="margin-right:10px">
رجوع
</a>

</form>

</div>

@endsection