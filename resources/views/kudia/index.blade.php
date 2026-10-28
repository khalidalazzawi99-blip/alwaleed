@extends('layouts.app')
@section('content')
@include('kudia._styles')
<div class="kudia-page">
    @include('kudia._nav')
    <section class="kudia-stats">
        <div class="kudia-stat"><span>إجمالي الفواتير الفعالة</span><strong><bdi>{{ number_format($invoiceTotal, 2) }}</bdi> <small>{{ $currency }}</small></strong></div>
        <div class="kudia-stat"><span>عدد الفواتير الفعالة</span><strong>{{ $invoiceCount }}</strong></div>
        <div class="kudia-stat"><span>الزبائن</span><strong>{{ $customerCount }}</strong></div>
    </section>
    <section class="kudia-panel">
        <div class="kudia-toolbar"><h2>فواتير المصنع</h2><form method="GET"><input name="q" value="{{ request('q') }}" placeholder="رقم الفاتورة أو اسم الزبون" aria-label="بحث الفواتير"><button type="submit">بحث</button></form></div>
        <div style="overflow-x:auto"><table class="kudia-table"><thead><tr><th>رقم الفاتورة</th><th>التاريخ</th><th>الزبون</th><th>الإجمالي</th><th>الحالة</th><th>الفاتورة</th></tr></thead><tbody>
        @forelse($invoices as $invoice)
            <tr><td><a href="{{ route('kudia.invoices.show', $invoice) }}"><bdi>{{ $invoice->invoice_no }}</bdi></a></td><td>{{ $invoice->invoice_date->format('Y/m/d') }}</td><td>{{ $invoice->payload['client_name'] ?? $invoice->customer?->name }}</td><td><bdi>{{ number_format($invoice->amount, 2) }}</bdi> {{ $invoice->currency }}</td><td><span class="kudia-pill {{ $invoice->status === 'cancelled' ? 'cancelled' : '' }}">{{ $invoice->status === 'cancelled' ? 'ملغاة' : 'فعالة' }}</span></td><td><a href="{{ route('kudia.invoices.pdf', $invoice) }}">PDF</a></td></tr>
        @empty
            <tr><td colspan="6"><div class="kudia-empty">ماكو فواتير لحد الآن. <a href="{{ route('kudia.invoices.create') }}">أنشئ أول فاتورة</a></div></td></tr>
        @endforelse
        </tbody></table></div>
        {{ $invoices->links() }}
    </section>
</div>
@endsection
