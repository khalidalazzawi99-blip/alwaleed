@php
    $asset = fn ($name) => 'data:image/svg+xml;base64,'.base64_encode(file_get_contents(public_path('images/kudia-'.$name.'.svg')));
    $money = fn ($value) => number_format((float) $value, (float) $value != 0 && floor((float) $value) == (float) $value ? 0 : 2);
    $paymentInfo = $movements->filter(fn ($movement) => $movement->received > 0 && filled($movement->notes))->pluck('notes')->unique()->implode("\n");
    if (!$paymentInfo && $totalReceived > 0) {
        $paymentInfo = $balance > 0 ? 'Partial payment received from customer.' : 'Payment received from customer.';
    }
@endphp
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>KUDIA — Statement of Account — {{ $customer->name }}</title>
    @include('kudia._print-styles')
    <style>
        .statement-sheet .brand-logo-cell { width: 34%; padding-left: 18mm; }
        .statement-sheet .statement-heading-cell { width: 68%; padding-left: 10mm; }
        .statement-sheet .client-info { padding-top: 16mm; }
        .statement-sheet .title-first-line { width: 98mm; height: 13mm; }
        .statement-sheet .title-second-line { width: 73mm; height: 14mm; margin-top: 2mm; }
        .statement-grid .date-column { width: 13%; }
        .statement-grid .reference-column { width: 11%; }
        .statement-grid .description-column { width: 28%; }
        .statement-grid .debit-column { width: 16%; }
        .statement-grid .payment-column { width: 15%; }
        .statement-grid .balance-column { width: 17%; }
        .statement-grid .description { font-size: 13px; }
        .statement-grid tbody td:first-child .cell { font-size: 13px; white-space: nowrap; }
        .statement-grid .reference-detail { font-size: 7px; line-height: 1.2; white-space: nowrap; }
    </style>
</head>
<body>
<main class="kudia-sheet statement-sheet">
    <img class="kudia-corner" src="{{ $asset('corner') }}" alt="">
    <table class="kudia-brand"><tr><td class="brand-logo-cell"><img class="kudia-logo" src="{{ $asset('logo') }}" alt="KUDIA"></td><td class="brand-wordmark"><img src="{{ $asset('wordmark') }}" alt="KUDIA"></td></tr></table>
    <table class="kudia-intro"><tr>
        <td class="statement-heading-cell"><h1 class="kudia-heading"><img class="title-first-line" src="{{ $asset('statement-title') }}" alt="STATEMENT OF"><img class="title-second-line" src="{{ $asset('account-title') }}" alt="ACCOUNT"></h1><p class="issued-date">DATE ISSUED: {{ now()->format('d-m-Y') }}</p>@if($from || $to)<p class="statement-period">PERIOD: {{ $from ?: '—' }} — {{ $to ?: '—' }}</p>@endif</td>
        <td class="client-info"><div class="client-label">CLIENT INFO:</div><div class="client-name" dir="auto">{{ $customer->name }}</div><div class="client-address" dir="auto">{{ $customer->address }}</div></td>
    </tr></table>
    <table class="kudia-grid statement-grid">
        <thead><tr><th class="date-column">DATE</th><th class="reference-column">REF NO.</th><th class="description-column">DESCRIPTION</th><th class="debit-column">DEBIT</th><th class="payment-column">PAYMENT</th><th class="balance-column">BALANCE</th></tr></thead>
        <tbody>
        @if($from)
            <tr><td><div class="cell">{{ \Carbon\Carbon::parse($from)->format('j-n-Y') }}</div></td><td><div class="cell">—</div></td><td><div class="cell description">Opening Balance</div></td><td><div class="cell">—</div></td><td><div class="cell">—</div></td><td><div class="cell money">{{ $currency }} {{ $money($openingBalance) }}</div></td></tr>
        @endif
        @forelse($movements as $index => $movement)
            @php
                $debit = $movement->invoiced + $movement->paid;
                $description = $movement->description ?? match ($movement->resource) {
                    'receipt' => 'Customer Payment', 'payment' => 'Customer Refund',
                    default => $movement->received > 0 ? 'Debt Payment' : 'Customer Borrowing',
                };
                $reference = $movement->number;
                if (preg_match('/^KUD-\d{4}-(\d+)$/', $reference, $referenceParts)) {
                    $reference = $referenceParts[1];
                }
            @endphp
            <tr class="{{ $index % 2 ? 'stripe' : '' }}">
                <td><div class="cell">{{ \Carbon\Carbon::parse($movement->date)->format('j-n-Y') }}</div></td>
                <td><div class="cell">{{ $movement->received > 0 ? 'PAYMENT' : $reference }}@if($movement->received > 0)<span class="reference-detail">{{ $movement->number }}</span>@endif</div></td>
                <td><div class="cell description" dir="auto">{{ $description }}</div></td>
                <td><div class="cell money">{{ $debit ? $currency.' '.$money($debit) : '—' }}</div></td>
                <td><div class="cell money">{{ $movement->received ? $currency.' '.$money($movement->received) : '—' }}</div></td>
                <td><div class="cell money">{{ $currency }} {{ $money($movement->balance) }}</div></td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="cell">No transactions in this period.</div></td></tr>
        @endforelse
        </tbody>
    </table>
    <table class="kudia-settlement"><tr>
        <td class="kudia-payment-info"><strong>PAYMENT INFO:</strong><div class="payment-note" dir="auto">{{ $paymentInfo }}</div></td>
        <td><table class="kudia-totals">
            @if($from)<tr><td>OPENING BALANCE</td><td>{{ $currency }} {{ $money($openingBalance) }}</td></tr>@endif
            <tr><td>TOTAL INVOICES</td><td>{{ $currency }} {{ $money($totalInvoiceAmount) }}</td></tr>
            @if($totalBorrowed + $totalPaid != 0)<tr><td>OTHER DEBITS</td><td>{{ $currency }} {{ $money($totalBorrowed + $totalPaid) }}</td></tr>@endif
            <tr><td>PAYMENT RECEIVED</td><td>{{ $currency }} {{ $money($totalReceived) }}</td></tr>
            <tr class="grand"><td>BALANCE DUE</td><td>{{ $currency }} {{ $money($balance) }}</td></tr>
        </table></td>
    </tr></table>
    <div class="kudia-thanks"><img src="{{ $asset('thanks') }}" alt="Thank you for your business!"></div>
    <img class="kudia-footer-art" src="{{ $asset('footer') }}" alt="">
</main>
@if(!($pdfMode ?? false))<div class="print-actions"><button onclick="window.print()">طباعة كشف الحساب / Print</button></div>@endif
</body>
</html>
