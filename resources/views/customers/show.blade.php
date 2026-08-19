@extends('layouts.app')

@section('content')
@php
    $party = $customer;
    $statementTitle = __('messages.customer_statement');
    $printUrl = url('/customers/'.$customer->id.'/print');
    $resetUrl = url('/customers/'.$customer->id);
    $receiptUrl = url('/receipts?party_type=customer&party_id='.$customer->id);
    $paymentUrl = url('/payments?party_type=customer&party_id='.$customer->id);
@endphp
@include('statements.show')

<section class="card" style="margin-top:20px">
    <h2>{{ __('messages.external_invoices') }}</h2>
    <div style="overflow-x:auto">
        <table>
            <thead><tr><th>{{ __('messages.invoice_number') }}</th><th>{{ __('messages.date') }}</th><th>{{ __('messages.amount') }}</th></tr></thead>
            <tbody>
            @forelse($externalInvoicesPage as $invoice)
                <tr><td>{{ $invoice->invoice_no }}</td><td>{{ $invoice->invoice_date?->format('Y/m/d') }}</td><td>{{ number_format($invoice->amount, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">{{ __('messages.no_external_invoices') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $externalInvoicesPage->links() }}
</section>
@endsection
