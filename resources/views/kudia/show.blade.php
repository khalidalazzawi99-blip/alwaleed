@extends('layouts.app')
@section('content')
@include('kudia._styles')
<div class="kudia-page">
    @include('kudia._nav')
    <div class="kudia-toolbar">
        <h2><bdi>{{ $invoice->invoice_no }}</bdi> <span class="kudia-pill {{ $invoice->status === 'cancelled' ? 'cancelled' : '' }}">{{ $invoice->status === 'cancelled' ? 'ملغاة' : 'فعالة' }}</span></h2>
        <div class="kudia-nav"><a href="{{ route('kudia.invoices.pdf', $invoice) }}" class="active">تنزيل PDF</a><a href="{{ route('kudia.invoices.print', $invoice) }}" target="_blank" rel="noopener">طباعة</a>@if($invoice->status !== 'cancelled')<a href="{{ route('kudia.invoices.edit', $invoice) }}">تعديل</a>@endif @if($invoice->customer_id)<a href="{{ $invoice->customer?->account_group === \App\Models\Customer::GROUP_KUDIA ? route('kudia.customers.show', $invoice->customer_id) : url('/customers/'.$invoice->customer_id) }}">كشف حساب الزبون</a>@endif</div>
    </div>
    <iframe class="kudia-invoice-frame" src="{{ route('kudia.invoices.print', $invoice) }}?preview=1" title="معاينة فاتورة مصنع كوديا"></iframe>
    @if($invoice->status !== 'cancelled')
        <form method="POST" action="{{ route('kudia.invoices.destroy', $invoice) }}" class="kudia-cancel" data-confirm="هل تريد إلغاء الفاتورة؟ سيُعاد احتساب رصيد الزبون.">@csrf @method('DELETE')<label for="reason">سبب إلغاء الفاتورة</label><input id="reason" name="reason" maxlength="1000" required><button type="submit" style="margin-top:12px;background:#b91c1c">إلغاء الفاتورة</button></form>
    @else
        <div class="kudia-panel">سبب الإلغاء: {{ $invoice->payload['cancellation']['reason'] ?? '—' }}</div>
    @endif
</div>
@endsection
