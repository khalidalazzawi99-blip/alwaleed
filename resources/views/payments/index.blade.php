@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">{{ __('🔴 سند صرف') }}</h1>
    <p style="color:#64748B">{{ __('تسجيل المبالغ الخارجة من الصندوق') }}</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1.3fr;gap:22px">

<div class="card">
<h2>{{ __('سند جديد') }}</h2>

<form method="POST" action="/payments">
@csrf

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('رقم الوصل') }}</label>
<input type="text" id="paymentNumberPreview" value="{{ $nextPaymentNo }}" readonly>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('التاريخ') }}</label>
<input type="date" id="paymentDate" name="payment_date" value="{{ date('Y-m-d') }}" required>

<br><br>

<label style="font-weight:700;display:block;margin-bottom:20px;">{{ __('messages.party') }}</label>
<select id="paymentParty" required>
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
<input type="hidden" name="party_type" id="paymentPartyType">
<input type="hidden" name="party_id" id="paymentPartyId">

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

<button type="submit" class="danger" style="width:100%;padding:16px">
{{ __('حفظ سند الصرف') }}
</button>

</form>
</div>

<div class="card">
<h2>{{ __('سندات الصرف') }}</h2>
<input
    type="text"
    id="paymentSearch"
    placeholder="{{ __('بحث في سندات الصرف...') }}"
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
@foreach($payments as $payment)
<tr>
<td>{{ $payment->payment_no }}</td>
<td>{{ $payment->payment_date }}</td>
<td>{{ $payment->party?->name ?? '-' }} <small>({{ $payment->party_type === 'customer' ? __('messages.customer') : __('messages.supplier') }})</small></td>
<td>{{ $payment->cashbox?->name ?? __('الصندوق الرئيسي') }}</td>
<td style="color:#DC2626;font-weight:800">{{ number_format($payment->amount,2) }}</td>
<td>{{ $payment->notes }}</td>
<td>
    <div style="display:flex;gap:8px;align-items:center;justify-content:center;flex-wrap:nowrap;">

        <a href="/payments/{{ $payment->id }}/edit"
           class="btn"
           style="padding:10px 16px;white-space:nowrap;">
            {{ __('تعديل') }}
        </a>

        <a href="/payments/{{ $payment->id }}/print"
           class="btn"
           target="_blank"
           style="padding:10px 16px;white-space:nowrap;">
            {{ __('طباعة') }}
        </a>

        <a href="/payments/{{ $payment->id }}/pdf" class="btn" style="padding:10px 16px">PDF</a>
        <a href="/payments/{{ $payment->id }}/excel" class="btn" style="padding:10px 16px">Excel</a>

        <form action="/payments/{{ $payment->id }}"
              method="POST"
              style="display:inline;margin:0;">

            @csrf
            @method('DELETE')

            <button type="submit"
                    class="danger"
                    style="font-family:'Tajawal',sans-serif;padding:10px 16px;border-radius:14px;font-weight:700;background:#DC2626;color:white;border:none;white-space:nowrap;"
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
const nextPaymentNumbers = @json($nextPaymentNumbers);
const paymentCurrentYear = {{ now()->year }};
const paymentParty = document.getElementById('paymentParty');
const paymentDate = document.getElementById('paymentDate');
const paymentNumberPreview = document.getElementById('paymentNumberPreview');

function updatePaymentNumberPreview() {
    const year = paymentDate.value ? paymentDate.value.substring(0, 4) : paymentCurrentYear;
    const partyKey = paymentParty.value;
    const [type, id] = partyKey ? partyKey.split(':') : ['', ''];
    document.getElementById('paymentPartyType').value = type;
    document.getElementById('paymentPartyId').value = id;
    paymentNumberPreview.value = year == paymentCurrentYear && partyKey && nextPaymentNumbers[partyKey]
        ? nextPaymentNumbers[partyKey]
        : `PAY-${year}-000001`;
}

paymentParty.addEventListener('change', updatePaymentNumberPreview);
paymentDate.addEventListener('change', updatePaymentNumberPreview);
updatePaymentNumberPreview();

document.getElementById('paymentSearch').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');

    rows.forEach(function (row) {
        row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
});
</script>
@endsection
