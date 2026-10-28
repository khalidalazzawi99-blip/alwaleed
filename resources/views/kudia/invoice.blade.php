@php
    $details = $invoice->payload ?? [];
    $items = $details['items'] ?? [];
    $asset = fn ($name) => 'data:image/svg+xml;base64,'.base64_encode(file_get_contents(public_path('images/kudia-'.$name.'.svg')));
    $money = fn ($value) => number_format((float) $value, (float) $value != 0 && floor((float) $value) == (float) $value ? 0 : 2);
@endphp
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>KUDIA — {{ $invoice->invoice_no }}</title>
    <style>
        @font-face{font-family:Tajawal;src:url("data:font/truetype;base64,{{ base64_encode(file_get_contents(public_path('fonts/Tajawal-Regular.ttf'))) }}") format('truetype');font-weight:400}
        @font-face{font-family:Tajawal;src:url("data:font/truetype;base64,{{ base64_encode(file_get_contents(public_path('fonts/Tajawal-Bold.ttf'))) }}") format('truetype');font-weight:700}
        @page{size:A4 portrait;margin:16mm 20mm 34mm}
        *{box-sizing:border-box}body{margin:0;background:#eaf0f4;color:#082348;font-family:Tajawal,'DejaVu Sans',sans-serif;font-size:13px;line-height:1.35}.invoice-paper{position:relative;width:210mm;min-height:297mm;margin:20px auto;padding:16mm 20mm 34mm;background:#fff;overflow:hidden;box-shadow:0 15px 50px #c7d2dc}table{width:100%;border-collapse:collapse;direction:ltr}td,th{vertical-align:top}.corner{position:absolute;right:0;top:0;width:57mm;height:50mm}.identity{height:50mm;position:relative}.identity td{vertical-align:middle;padding:0}.identity .logo-cell{width:29%}.logo{width:26mm;height:25mm}.identity .wordmark{font-family:'DejaVu Sans',sans-serif;font-size:53px;font-weight:700;letter-spacing:1px;color:#002d43}.intro{margin:12mm 0 7mm}.intro .invoice-meta{width:62%}.invoice-heading{font-family:'DejaVu Sans',sans-serif;font-size:36px;font-weight:700;color:#00ace5;margin:0 0 1mm;letter-spacing:-1px}.intro p{margin:0;font-size:13px}.intro .label{font-size:13px;font-weight:700}.client-info{padding-top:5mm}.client-name{font-size:19px;font-weight:700;margin:0 0 2mm}.client-address{font-size:14px}.invoice-items{table-layout:fixed;border-collapse:separate;border-spacing:0 2mm;margin:0}.invoice-items thead{display:table-header-group}.invoice-items th{background:#003046;color:#fff;padding:10px 8px;font-size:13px;font-weight:700;text-align:center;vertical-align:middle}.invoice-items th:first-child{border-radius:22px 0 0 22px;text-align:left;padding-left:17px;width:45%}.invoice-items th:nth-child(2){width:19%}.invoice-items th:nth-child(3){width:17%}.invoice-items th:last-child{border-radius:0 22px 22px 0;width:19%}.invoice-items td{height:11mm;padding:6px 8px;font-size:13px;vertical-align:middle;word-wrap:break-word}.invoice-items td:first-child{padding-left:17px;border-radius:18px 0 0 18px}.invoice-items td:last-child{border-radius:0 18px 18px 0}.invoice-items td+td{border-left:1px solid #8a9ab1;text-align:center}.invoice-items tr.stripe td{background:#dce3e6}.item-name{font-weight:700;font-size:14px}.item-details{font-size:12px;white-space:pre-line}.item-amount{white-space:nowrap;font-size:12px}.invoice-items tr{page-break-inside:avoid}.settlement{margin-top:7mm;page-break-inside:avoid}.settlement .payment-info{width:57%;padding:4mm 6mm 0 4mm;white-space:pre-line}.payment-title{font-size:13px;font-weight:700}.totals{width:100%;font-size:14px}.totals td{padding:4px 7px;text-align:right;vertical-align:middle}.totals td+td{border-left:1px solid #a5b0c0;width:54%;white-space:nowrap}.totals .grand td{font-weight:700}.totals .grand td+td{background:#e9f8fe}.invoice-notes{margin:5mm 4mm 0;white-space:pre-line;font-size:12px;page-break-inside:avoid}.thanks{border-top:1px solid #00ace5;padding-top:6mm;margin-top:7mm;text-align:center;color:#00ace5;font-style:italic;font-size:18px;page-break-inside:avoid}.bottom-art{display:block;width:44mm;height:30mm;margin:7mm auto -34mm}.cancelled-stamp{border:2px solid #c33;color:#c33;text-align:center;padding:7px;font-weight:700;margin-bottom:10px}.print-actions{text-align:center;margin:20px}.print-actions button{padding:12px 25px;background:#003046;color:#fff;border:0;border-radius:8px;font:700 15px Tajawal;cursor:pointer}[dir=rtl]{direction:rtl;text-align:right}
        .identity .wordmark{font-size:72px}.invoice-heading{font-size:47px}.intro p,.intro .label{font-size:15px}.client-name{font-size:21px}.client-address{font-size:16px}.invoice-items th{font-size:15px}.invoice-items td{font-size:15px}.item-name{font-size:16px}.item-details{font-size:14px}.item-amount{font-size:14px}.payment-title{font-size:15px}.totals{font-size:16px}.bottom-art{position:absolute;bottom:0;left:83mm;margin:0}
        @media print{html,body{background:#fff;margin:0}.invoice-paper{width:auto;min-height:0;margin:0;padding:0;box-shadow:none;overflow:visible}.corner{top:-16mm;right:-20mm}.print-actions{display:none}.bottom-art{position:fixed;bottom:-34mm;left:63mm;margin:0}.identity{height:43mm}.intro{margin-top:10mm}}
    </style>
</head>
<body>
<main class="invoice-paper">
    <img class="corner" src="{{ $asset('corner') }}" alt="">
    <table class="identity"><tr><td class="logo-cell"><img class="logo" src="{{ $asset('logo') }}" alt="KUDIA"></td><td class="wordmark">KUDIA</td></tr></table>
    @if($invoice->status === 'cancelled')<div class="cancelled-stamp">CANCELLED / فاتورة ملغاة</div>@endif
    <table class="intro"><tr>
        <td class="invoice-meta"><h1 class="invoice-heading">INVOICE</h1><p><strong>INVOICE # &nbsp; {{ $invoice->invoice_no }}</strong></p><p>DATE ISSUED: {{ $invoice->invoice_date->format('d-m-Y') }}</p></td>
        <td class="client-info"><div class="label">CLIENT INFO:</div><div class="client-name" dir="auto">{{ $details['client_name'] ?? $invoice->customer?->name }}</div><div class="client-address" dir="auto">{{ $details['client_address'] ?? '' }}</div>@if(!empty($details['client_phone']))<div>{{ $details['client_phone'] }}</div>@endif</td>
    </tr></table>
    <table class="invoice-items">
        <thead><tr><th>ITEM DESCRIPTION</th><th>PRICE</th><th>QTY</th><th>TOTAL</th></tr></thead>
        <tbody>
        @foreach($items as $index => $item)
            <tr class="{{ $index % 2 ? 'stripe' : '' }}"><td><div class="item-name" dir="auto">{{ $item['description'] }}</div><div class="item-details" dir="auto">{{ $item['details'] ?? '' }}</div></td><td><span class="item-amount">{{ $invoice->currency }} {{ $money($item['unit_price']) }}</span></td><td>{{ rtrim(rtrim(number_format($item['quantity'], 3, '.', ''), '0'), '.') }} {{ $item['unit'] ?? '' }}</td><td><span class="item-amount">{{ $invoice->currency }} {{ $money($item['total']) }}</span></td></tr>
        @endforeach
        @for($blank = count($items); $blank < 4; $blank++)
            <tr class="stripe"><td>&nbsp;</td><td></td><td></td><td></td></tr>
        @endfor
        </tbody>
    </table>
    <table class="settlement"><tr>
        <td class="payment-info"><div class="payment-title">PAYMENT INFO:</div><div dir="auto">{{ $details['payment_info'] ?? '' }}</div></td>
        <td><table class="totals"><tr><td>SUBTOTAL</td><td>{{ $invoice->currency }} {{ $money($invoice->subtotal) }}</td></tr><tr><td>TAX</td><td>{{ $invoice->currency }} {{ $money($details['tax_amount'] ?? 0) }}</td></tr><tr class="grand"><td>TOTAL PRICE</td><td>{{ $invoice->currency }} {{ $money($invoice->amount) }}</td></tr></table></td>
    </tr></table>
    @if($invoice->notes)<div class="invoice-notes" dir="auto">{{ $invoice->notes }}</div>@endif
    <div class="thanks">Thank you for your business!</div>
    <img class="bottom-art" src="{{ $asset('footer') }}" alt="">
</main>
@if(!($pdfMode ?? false) && !request()->boolean('preview'))<div class="print-actions"><button onclick="window.print()">طباعة الفاتورة / Print</button></div>@endif
</body>
</html>
