@extends('layouts.app')

@section('content')
@if(request()->routeIs('kudia.*'))
    @include('kudia._styles')
    <div class="kudia-page" style="margin-bottom:24px">@include('kudia._nav')</div>
@endif
@php
    $party = $customer;
    $statementTitle = __('messages.customer_statement');
    $statementBase = request()->routeIs('kudia.*') ? '/kudia/customers/' : '/customers/';
    $printUrl = url($statementBase.$customer->id.'/print');
    $resetUrl = url($statementBase.$customer->id);
    $receiptUrl = url('/receipts?party_type=customer&party_id='.$customer->id);
    $paymentUrl = url('/payments?party_type=customer&party_id='.$customer->id);
    $partyType = 'customer';
@endphp
@include('statements.show')

<section class="card" style="margin-top:20px">
    <h2>{{ request()->routeIs('kudia.*') ? 'فواتير الزبون' : __('messages.external_invoices') }}</h2>
    <div style="overflow-x:auto">
        <table>
            <thead><tr><th>{{ __('messages.invoice_number') }}</th><th>{{ __('messages.date') }}</th><th>{{ __('messages.amount') }}</th></tr></thead>
            <tbody>
            @forelse($externalInvoicesPage as $invoice)
                <tr><td>@if($invoice->provider === 'kudia')<a href="{{ route('kudia.invoices.show', $invoice) }}">{{ $invoice->invoice_no }}</a>@else{{ $invoice->invoice_no }}@endif</td><td>{{ $invoice->invoice_date?->format('Y/m/d') }}</td><td>{{ number_format($invoice->amount, 2) }} {{ $companyCurrency }} @if($invoice->status === 'cancelled')<small>ملغاة</small>@endif</td></tr>
            @empty
                <tr><td colspan="3">{{ __('messages.no_external_invoices') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $externalInvoicesPage->links() }}
</section>
@endsection
