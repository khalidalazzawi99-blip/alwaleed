@extends('layouts.app')

@section('content')
<style>
.global-search{display:grid;gap:18px}.search-hero{background:linear-gradient(135deg,#182139,#2d3b57);padding:27px;border-radius:26px;color:#fff}.search-hero h1{margin:0 0 6px;font-size:27px}.search-hero p{margin:0;color:#cbd5e1}.search-form-large{display:flex;gap:10px;margin-top:20px}.search-form-large input{background:#fff;color:#172033;border:0}.search-form-large button{min-width:100px}.result-summary{display:flex;justify-content:space-between;align-items:center}.result-summary h2{margin:0;font-size:20px}.result-summary span{color:var(--text-soft);font-size:13px}.result-sections{display:grid;grid-template-columns:1fr 1fr;gap:18px}.result-section{background:var(--surface);border:1px solid var(--border);border-radius:22px;padding:18px}.result-section h3{margin:0 0 13px;display:flex;justify-content:space-between;font-size:17px}.result-section h3 span{font-size:11px;color:var(--text-soft);background:var(--surface-soft);padding:5px 8px;border-radius:9px}.result-list{display:grid;gap:8px}.result-item{display:flex;align-items:center;justify-content:space-between;gap:12px;background:var(--surface-soft);border:1px solid var(--border);border-radius:15px;padding:13px;text-decoration:none;color:var(--text);transition:.2s}.result-item:hover{border-color:var(--accent);transform:translateY(-1px)}.result-main{min-width:0}.result-title{font-weight:900;overflow:hidden;text-overflow:ellipsis}.result-meta{font-size:12px;color:var(--text-soft);margin-top:4px}.result-amount{font-weight:900;white-space:nowrap}.result-amount.in{color:#15803d}.result-amount.out{color:#b91c1c}.empty-search{grid-column:1/-1;text-align:center;background:var(--surface);border:1px solid var(--border);border-radius:22px;padding:50px;color:var(--text-soft)}
@media(max-width:800px){.result-sections{grid-template-columns:1fr}.search-form-large{flex-direction:column}.result-summary{align-items:flex-start;flex-direction:column;gap:5px}}
</style>
@php
    $total = $customers->count()+$suppliers->count()+$receipts->count()+$payments->count();
    $role = auth()->user()->role;
    $canOpenParties = in_array($role, ['super_admin','admin','data_entry']);
    $canOpenVouchers = in_array($role, ['super_admin','admin','accountant']);
@endphp
<div class="global-search">
    <header class="search-hero">
        <h1>{{ __('messages.global_search') }}</h1><p>{{ __('messages.search_hint') }}</p>
        <form action="/search" method="GET" class="search-form-large"><input type="search" name="q" value="{{ $term }}" autofocus placeholder="{{ __('messages.search') }}"><button>{{ __('messages.search_now') }}</button></form>
    </header>
    @if($term !== '')
        <div class="result-summary"><h2>{{ __('messages.search_results_for', ['term'=>$term]) }}</h2><span>{{ __('messages.results_count', ['count'=>$total]) }}</span></div>
        <div class="result-sections">
            @if($total === 0)<div class="empty-search">{{ __('messages.no_search_results') }}</div>@endif
            @if($customers->isNotEmpty())<section class="result-section"><h3>{{ __('messages.customers') }} <span>{{ $customers->count() }}</span></h3><div class="result-list">@foreach($customers as $customer)<a id="customer-{{ $customer->id }}" href="{{ $canOpenParties ? '/customers/'.$customer->id : '#customer-'.$customer->id }}" class="result-item"><div class="result-main"><div class="result-title">{{ $customer->name }}</div><div class="result-meta">{{ $customer->phone ?: '—' }} · {{ $customer->company_name ?: '—' }}</div></div><span>←</span></a>@endforeach</div></section>@endif
            @if($suppliers->isNotEmpty())<section class="result-section"><h3>{{ __('messages.suppliers') }} <span>{{ $suppliers->count() }}</span></h3><div class="result-list">@foreach($suppliers as $supplier)<a id="supplier-{{ $supplier->id }}" href="{{ $canOpenParties ? '/suppliers/'.$supplier->id : '#supplier-'.$supplier->id }}" class="result-item"><div class="result-main"><div class="result-title">{{ $supplier->name }}</div><div class="result-meta">{{ $supplier->phone ?: '—' }} · {{ $supplier->company_name ?: '—' }}</div></div><span>←</span></a>@endforeach</div></section>@endif
            @if($receipts->isNotEmpty())<section class="result-section"><h3>{{ __('messages.receipts') }} <span>{{ $receipts->count() }}</span></h3><div class="result-list">@foreach($receipts as $receipt)<a id="receipt-{{ $receipt->id }}" href="{{ $canOpenVouchers ? '/receipts/'.$receipt->id.'/print' : '#receipt-'.$receipt->id }}" @if($canOpenVouchers) target="_blank" @endif class="result-item"><div class="result-main"><div class="result-title">{{ $receipt->receipt_no }}</div><div class="result-meta">{{ $receipt->party?->name ?: '—' }} · {{ $receipt->receipt_date }}</div></div><div class="result-amount in">{{ number_format($receipt->amount,2) }}</div></a>@endforeach</div></section>@endif
            @if($payments->isNotEmpty())<section class="result-section"><h3>{{ __('messages.payments') }} <span>{{ $payments->count() }}</span></h3><div class="result-list">@foreach($payments as $payment)<a id="payment-{{ $payment->id }}" href="{{ $canOpenVouchers ? '/payments/'.$payment->id.'/print' : '#payment-'.$payment->id }}" @if($canOpenVouchers) target="_blank" @endif class="result-item"><div class="result-main"><div class="result-title">{{ $payment->payment_no }}</div><div class="result-meta">{{ $payment->party?->name ?: '—' }} · {{ $payment->payment_date }}</div></div><div class="result-amount out">{{ number_format($payment->amount,2) }}</div></a>@endforeach</div></section>@endif
            @if($externalInvoices->isNotEmpty())<section class="result-section"><h3>{{ __('messages.external_invoices') }} <span>{{ $externalInvoices->count() }}</span></h3><div class="result-list">@foreach($externalInvoices as $invoice)<a href="/external-invoices" class="result-item"><div class="result-main"><div class="result-title">{{ $invoice->invoice_no }}</div><div class="result-meta">{{ $invoice->customer?->name ?: __('messages.unlinked') }} · {{ $invoice->invoice_date?->format('Y/m/d') }}</div></div><div class="result-amount in">{{ number_format($invoice->amount,2) }}</div></a>@endforeach</div></section>@endif
        </div>
    @else
        <div class="empty-search">{{ __('messages.start_searching') }}</div>
    @endif
</div>
@endsection
