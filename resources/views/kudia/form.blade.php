@extends('layouts.app')
@section('content')
@include('kudia._styles')
@php
    $lineItems = old('items', $invoice?->payload['items'] ?? [['description' => '', 'details' => '', 'unit' => 'cartons', 'quantity' => 1, 'unit_price' => '']]);
@endphp
<div class="kudia-page">
    @include('kudia._nav')
    @if($errors->any())<div class="kudia-errors"><strong>يرجى مراجعة المعلومات:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ $invoice ? route('kudia.invoices.update', $invoice) : route('kudia.invoices.store') }}" class="kudia-panel" id="kudiaInvoiceForm">
        @csrf
        @if($invoice) @method('PUT') @endif
        <div class="kudia-toolbar"><h2>{{ $invoice ? 'تعديل فاتورة '.$invoice->invoice_no : 'فاتورة جديدة' }}</h2><span>العملة: {{ $currency }}</span></div>
        <div class="kudia-form-grid" style="margin:24px 0">
            <div class="kudia-field"><label for="customer_id">الزبون</label><select name="customer_id" id="customer_id" required><option value="">اختر الزبون</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected((string) old('customer_id', $invoice?->customer_id) === (string) $customer->id)>{{ $customer->name }}</option>@endforeach</select><small><a href="{{ route('kudia.customers.index') }}">إضافة زبون جديد</a></small></div>
            <div class="kudia-field"><label for="invoice_date">تاريخ الفاتورة</label><input type="date" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', $invoice?->invoice_date?->toDateString() ?? now()->toDateString()) }}" required></div>
        </div>
        <h3>تفاصيل المواد</h3>
        <div id="kudiaLines">
            @foreach($lineItems as $i => $item)
                @include('kudia._line', ['index' => $i, 'item' => $item])
            @endforeach
        </div>
        <button type="button" id="addKudiaLine" class="btn">+ إضافة مادة</button>
        <div class="kudia-form-grid" style="margin-top:24px">
            <div class="kudia-field"><label for="tax_rate">الضريبة %</label><input type="number" id="tax_rate" name="tax_rate" min="0" max="100" step="0.01" value="{{ old('tax_rate', $invoice?->payload['tax_rate'] ?? 0) }}" required></div>
            <div class="kudia-field"><label for="payment_info">معلومات الدفع</label><textarea name="payment_info" id="payment_info" rows="3" maxlength="2000" placeholder="تفاصيل الحساب البنكي أو تعليمات الدفع">{{ old('payment_info', $invoice?->payload['payment_info'] ?? '') }}</textarea></div>
            <div class="kudia-field wide"><label for="notes">ملاحظات الفاتورة</label><textarea name="notes" id="notes" rows="2" maxlength="2000">{{ old('notes', $invoice?->notes) }}</textarea></div>
        </div>
        <div class="kudia-totals"><div><span>المجموع الفرعي</span><bdi id="subtotalPreview">0.00</bdi></div><div><span>الضريبة</span><bdi id="taxPreview">0.00</bdi></div><div class="grand"><span>الإجمالي</span><bdi><span id="totalPreview">0.00</span> {{ $currency }}</bdi></div></div>
        <div class="kudia-toolbar" style="margin-top:25px"><button type="submit">حفظ الفاتورة</button><a class="btn" href="{{ route('kudia.index') }}">رجوع</a></div>
    </form>
</div>
<template id="kudiaLineTemplate">@include('kudia._line', ['index' => '__INDEX__', 'item' => ['quantity' => 1, 'unit' => 'cartons']])</template>
<script>
(() => {
    const lines = document.getElementById('kudiaLines');
    let nextIndex = {{ count($lineItems) ? max(array_map('intval', array_keys($lineItems))) + 1 : 0 }};
    const formatter = new Intl.NumberFormat('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    function calculate() {
        let subtotal = 0;
        lines.querySelectorAll('.kudia-line').forEach(line => {
            const price = Number(line.querySelector('[data-price]').value) || 0;
            const quantity = Number(line.querySelector('[data-quantity]').value) || 0;
            const amount = Math.round((price * quantity + Number.EPSILON) * 100) / 100;
            line.querySelector('[data-line-total]').textContent = formatter.format(amount);
            subtotal += amount;
        });
        const tax = Math.round(subtotal * (Number(document.getElementById('tax_rate').value) || 0)) / 100;
        document.getElementById('subtotalPreview').textContent = formatter.format(subtotal);
        document.getElementById('taxPreview').textContent = formatter.format(tax);
        document.getElementById('totalPreview').textContent = formatter.format(subtotal + tax);
    }
    document.getElementById('addKudiaLine').addEventListener('click', () => {
        if (lines.children.length >= 100) return;
        lines.insertAdjacentHTML('beforeend', document.getElementById('kudiaLineTemplate').innerHTML.replaceAll('__INDEX__', nextIndex++));
        calculate();
    });
    lines.addEventListener('click', event => { if(event.target.closest('[data-remove]') && lines.children.length > 1) { event.target.closest('.kudia-line').remove(); calculate(); } });
    document.getElementById('kudiaInvoiceForm').addEventListener('input', calculate);
    calculate();
})();
</script>
@endsection
