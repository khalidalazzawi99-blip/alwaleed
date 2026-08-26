@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">{{ __('🟢 سند قبض') }}</h1>
    <p style="color:#64748B">{{ __('تسجيل المبالغ الداخلة إلى الصندوق') }}</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1.3fr;gap:22px">

<div class="card">
<h2>{{ __('سند جديد') }}</h2>

<form method="POST" action="/receipts">
@csrf

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('رقم الوصل') }}</label>
<input type="text" id="receiptNumberPreview" value="{{ $nextReceiptNo }}" readonly>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('التاريخ') }}</label>
<input type="date" id="receiptDate" name="receipt_date" value="{{ date('Y-m-d') }}" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('messages.party') }}</label>
<select id="receiptParty" required>
<option value="">{{ __('messages.choose_party') }}</option>
<optgroup label="{{ __('messages.customers') }}">
@foreach($customers as $customer)
<option value="customer:{{ $customer->id }}" @selected(request('party_type') === 'customer' && (int) request('party_id') === $customer->id)>{{ $customer->name }}</option>
@endforeach
</optgroup>
<optgroup label="{{ __('messages.suppliers') }}">
@foreach($suppliers as $supplier)
<option value="supplier:{{ $supplier->id }}" @selected(request('party_type') === 'supplier' && (int) request('party_id') === $supplier->id)>{{ $supplier->name }}</option>
@endforeach
</optgroup>
</select>
<input type="hidden" name="party_type" id="receiptPartyType">
<input type="hidden" name="party_id" id="receiptPartyId">

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('الصندوق') }}</label>
<select name="cashbox_id" required>
@foreach($cashboxes as $cashbox)
<option value="{{ $cashbox->id }}" @selected((int) old('cashbox_id', request('cashbox_id', $cashboxes->first()?->id)) === $cashbox->id)>{{ $cashbox->name }} — {{ number_format($cashbox->balance, 2) }}</option>
@endforeach
</select>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('المبلغ') }}</label>
<input type="number" name="amount" step="0.01" placeholder="250000" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('الملاحظات') }}</label>
<textarea name="notes" rows="4" placeholder="{{ __('اكتب ملاحظات السند') }}"></textarea>

<br><br>

<button type="submit" class="success" style="width:100%;padding:16px">
{{ __('حفظ سند القبض') }}
</button>

</form>
</div>

<div class="card">
<h2>{{ __('سندات القبض') }}</h2>
<input
    type="text"
    id="receiptSearch"
    placeholder="{{ __('بحث في سندات القبض...') }}"
    style="margin-bottom:20px">
<table>
<thead>
<tr>
<th>{{ __('رقم الوصل') }}</th>
<th>{{ __('التاريخ') }}</th>
<th>{{ __('messages.party') }}</th>
<th>{{ __('الصندوق') }}</th>
<th>{{ __('المبلغ') }}</th>
<th>{{ __('الملاحظات') }}</th>
<th>{{ __('طباعة') }}</th>
</tr>
</thead>

<tbody>
@foreach($receipts as $receipt)
<tr>
<td>{{ $receipt->receipt_no }}</td>
<td>{{ $receipt->receipt_date }}</td>
<td>{{ $receipt->party?->name ?? '-' }} <small>({{ $receipt->party_type === 'customer' ? __('messages.customer') : __('messages.supplier') }})</small></td>
<td>{{ $receipt->cashbox?->name ?? __('الصندوق الرئيسي') }}</td>
<td style="color:#16A34A;font-weight:800">{{ number_format($receipt->amount,2) }}</td>
<td>{{ $receipt->notes }}</td>
<td>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">

    <a href="/receipts/{{ $receipt->id }}/edit"
       class="btn"
       style="margin-left:10px;">
       {{ __('تعديل') }}
    </a>

    <a href="/receipts/{{ $receipt->id }}/print"
       class="btn"
       target="_blank"
       style="margin-left:10px;">
       {{ __('طباعة') }}
    </a>

    <a href="/receipts/{{ $receipt->id }}/pdf" class="btn">PDF</a>
    <a href="/receipts/{{ $receipt->id }}/excel" class="btn">Excel</a>

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
    onclick="return confirm(@js(__('هل أنت متأكد من حذف السند؟')))"
>

    {{ __('حذف') }}

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
const nextReceiptNumbers = @json($nextReceiptNumbers);
const receiptCurrentYear = {{ now()->year }};
const receiptParty = document.getElementById('receiptParty');
const receiptDate = document.getElementById('receiptDate');
const receiptNumberPreview = document.getElementById('receiptNumberPreview');

function updateReceiptNumberPreview() {
    const year = receiptDate.value ? receiptDate.value.substring(0, 4) : receiptCurrentYear;
    const partyKey = receiptParty.value;
    const [type, id] = partyKey ? partyKey.split(':') : ['', ''];
    document.getElementById('receiptPartyType').value = type;
    document.getElementById('receiptPartyId').value = id;
    receiptNumberPreview.value = year == receiptCurrentYear && partyKey && nextReceiptNumbers[partyKey]
        ? nextReceiptNumbers[partyKey]
        : `RCP-${year}-000001`;
}

receiptParty.addEventListener('change', updateReceiptNumberPreview);
receiptDate.addEventListener('change', updateReceiptNumberPreview);
updateReceiptNumberPreview();

document.getElementById('receiptSearch').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');

    rows.forEach(function (row) {
        row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
});
</script>
@endsection
