@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">{{ __('تعديل سند قبض') }}</h1>
    <p style="color:#8A8178">{{ __('تعديل بيانات السند وتحديث رصيد الصندوق تلقائياً') }}</p>
</div>

<div class="card">

<form method="POST" action="/receipts/{{ $receipt->id }}">
@csrf
@method('PUT')

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('رقم الوصل') }}</label>
<input type="text" value="{{ $receipt->receipt_no }}" readonly>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('التاريخ') }}</label>
<input type="date" name="receipt_date" value="{{ $receipt->receipt_date }}" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('messages.party') }}</label>
<select id="receiptPartyEdit" required>
<optgroup label="{{ __('messages.customers') }}">
@foreach($customers as $customer)
<option value="customer:{{ $customer->id }}" {{ $receipt->customer_id == $customer->id ? 'selected' : '' }}>
{{ $customer->name }}
</option>
@endforeach
</optgroup>
<optgroup label="{{ __('messages.suppliers') }}">
@foreach($suppliers as $supplier)
<option value="supplier:{{ $supplier->id }}" {{ $receipt->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
@endforeach
</optgroup>
</select>
<input type="hidden" name="party_type" id="receiptPartyTypeEdit" value="{{ $receipt->party_type }}">
<input type="hidden" name="party_id" id="receiptPartyIdEdit" value="{{ $receipt->party?->id }}">

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('المبلغ') }}</label>
<input type="number" name="amount" step="0.01" value="{{ $receipt->amount }}" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('الملاحظات') }}</label>
<textarea name="notes" rows="4">{{ $receipt->notes }}</textarea>

<br><br>

<button type="submit" class="success" style="padding:16px 35px">
{{ __('حفظ التعديل') }}
</button>

<a href="/receipts" class="btn" style="margin-right:10px">
{{ __('رجوع') }}
</a>

</form>

</div>

<script>
const receiptPartyEdit = document.getElementById('receiptPartyEdit');
receiptPartyEdit.addEventListener('change', function(){
    const [type, id] = this.value.split(':');
    document.getElementById('receiptPartyTypeEdit').value = type;
    document.getElementById('receiptPartyIdEdit').value = id;
});
</script>

@endsection
