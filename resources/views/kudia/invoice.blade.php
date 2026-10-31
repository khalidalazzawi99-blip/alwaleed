@php
    $details = $invoice->payload ?? [];
    $items = $details['items'] ?? [];
    $asset = fn ($name) => 'data:image/svg+xml;base64,'.base64_encode(file_get_contents(public_path('images/kudia-'.$name.'.svg')));
    $money = fn ($value) => number_format((float) $value, (float) $value != 0 && floor((float) $value) == (float) $value ? 0 : 2);
    $reference = preg_match('/^KUD-\d{4}-(\d+)$/', $invoice->invoice_no, $referenceParts) ? $referenceParts[1] : $invoice->invoice_no;
@endphp
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>KUDIA — {{ $invoice->invoice_no }}</title>
    @include('kudia._print-styles')
    <style>
        @page { margin: 12mm 25mm 35mm; }
        .invoice-sheet .kudia-corner { right: -25mm; }
        .invoice-sheet .kudia-intro { margin-top: 26mm; }
        .invoice-sheet .invoice-meta { width: 72%; }
        .invoice-sheet .client-info { padding-top: 2mm; }
        .invoice-sheet .client-name { font-size: 19px; }
        .invoice-sheet .kudia-heading img { width: 47mm; height: 11mm; }
        .invoice-sheet .invoice-reference { font-size: 15px; margin: 3mm 0 0; }
        .invoice-sheet .issued-date { margin-top: 0; }
        .invoice-grid .item-description { width: 45%; text-align: left; padding-left: 14px; }
        .invoice-grid .price-column { width: 19%; }
        .invoice-grid .quantity-column { width: 17%; }
        .invoice-grid .total-column { width: 19%; }
        .invoice-grid .description { padding-left: 14px; }
        .invoice-grid .item-name { font-weight: 700; font-size: 16px; }
        .invoice-grid .item-details { font-size: 14px; white-space: pre-line; }
        .invoice-sheet .kudia-payment-info { width: 57%; border-right: 0; }
        .invoice-sheet .kudia-totals { font-size: 15px; }
        .invoice-sheet .kudia-totals td { padding: 7px 6px; }
        .invoice-sheet .kudia-totals .grand td { font-size: 15px; }
        .invoice-sheet .kudia-totals .grand td:first-child { background: transparent; border-radius: 0; }
        .invoice-sheet .kudia-totals .grand td:last-child { border-radius: 0; }
        .invoice-sheet .kudia-footer-art { left: 58mm; }
        .invoice-notes { white-space: pre-line; margin: 5mm 4mm 0; font-size: 13px; page-break-inside: avoid; }
        .cancelled-stamp { border: 2px solid #c33; color: #c33; text-align: center; padding: 7px; font-weight: 700; margin-bottom: 10px; }
        @media screen { .kudia-sheet.invoice-sheet { padding-left: 25mm; padding-right: 25mm; } .invoice-sheet .kudia-corner { right: 0; } .invoice-sheet .kudia-footer-art { left: 83mm; } }
    </style>
</head>
<body>
<main class="kudia-sheet invoice-sheet">
    <img class="kudia-corner" src="{{ $asset('corner') }}" alt="">
    <table class="kudia-brand"><tr><td class="brand-logo-cell"><img class="kudia-logo" src="{{ $asset('logo') }}" alt="KUDIA"></td><td class="brand-wordmark"><img src="{{ $asset('wordmark') }}" alt="KUDIA"></td></tr></table>
    @if($invoice->status === 'cancelled')<div class="cancelled-stamp">CANCELLED / فاتورة ملغاة</div>@endif
    <table class="kudia-intro"><tr>
        <td class="invoice-meta"><h1 class="kudia-heading"><img src="{{ $asset('invoice-title') }}" alt="INVOICE"></h1><p class="invoice-reference"><strong>INVOICE # &nbsp; {{ $reference }}</strong></p><p class="issued-date">DATE ISSUED: {{ $invoice->invoice_date->format('d-m-Y') }}</p></td>
        <td class="client-info"><div class="client-label">CLIENT INFO:</div><div class="client-name" dir="auto">{{ $details['client_name'] ?? $invoice->customer?->name }}</div><div class="client-address" dir="auto">{{ $details['client_address'] ?? '' }}</div>@if(!empty($details['client_phone']))<div>{{ $details['client_phone'] }}</div>@endif</td>
    </tr></table>
    <table class="kudia-grid invoice-grid">
        <thead><tr><th class="item-description">ITEM DESCRIPTION</th><th class="price-column">PRICE</th><th class="quantity-column">QTY</th><th class="total-column">TOTAL</th></tr></thead>
        <tbody>
        @foreach($items as $index => $item)
            <tr class="{{ $index % 2 ? 'stripe' : '' }}"><td><div class="cell description"><div class="item-name" dir="auto">{{ $item['description'] }}</div><div class="item-details" dir="auto">{{ $item['details'] ?? '' }}</div></div></td><td><div class="cell money">{{ $invoice->currency }} {{ $money($item['unit_price']) }}</div></td><td><div class="cell">{{ rtrim(rtrim(number_format($item['quantity'], 3, '.', ''), '0'), '.') }} {{ $item['unit'] ?? '' }}</div></td><td><div class="cell money">{{ $invoice->currency }} {{ $money($item['total']) }}</div></td></tr>
        @endforeach
        @for($blank = count($items); $blank < 4; $blank++)
            <tr class="stripe"><td><div class="cell">&nbsp;</div></td><td><div class="cell">&nbsp;</div></td><td><div class="cell">&nbsp;</div></td><td><div class="cell">&nbsp;</div></td></tr>
        @endfor
        </tbody>
    </table>
    <table class="kudia-settlement"><tr>
        <td class="kudia-payment-info"><strong>PAYMENT INFO:</strong><div class="payment-note" dir="auto">{{ $details['payment_info'] ?? '' }}</div></td>
        <td><table class="kudia-totals"><tr><td>SUBTOTAL</td><td>{{ $invoice->currency }} {{ $money($invoice->subtotal) }}</td></tr><tr><td>TAX</td><td>{{ $invoice->currency }} {{ $money($details['tax_amount'] ?? 0) }}</td></tr><tr class="grand"><td>TOTAL PRICE</td><td>{{ $invoice->currency }} {{ $money($invoice->amount) }}</td></tr></table></td>
    </tr></table>
    @if($invoice->notes)<div class="invoice-notes" dir="auto">{{ $invoice->notes }}</div>@endif
    <div class="kudia-thanks"><img src="{{ $asset('thanks') }}" alt="Thank you for your business!"></div>
    <img class="kudia-footer-art" src="{{ $asset('footer') }}" alt="">
</main>
@if(!($pdfMode ?? false) && !request()->boolean('preview'))<div class="print-actions"><button onclick="window.print()">طباعة الفاتورة / Print</button></div>@endif
</body>
</html>
