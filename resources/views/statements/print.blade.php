<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $statementTitle }} - {{ $party->name }}</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
@page{size:A4;margin:10mm}*{box-sizing:border-box}body{margin:0;background:#e9edf3;color:#172033;font-family:'Tajawal',Arial,sans-serif}.sheet{width:210mm;min-height:297mm;margin:18px auto;background:#fff;padding:15mm;box-shadow:0 20px 60px rgba(15,23,42,.14);position:relative;overflow:hidden}.sheet:before{content:"";position:absolute;top:0;inset-inline-start:0;width:100%;height:7px;background:linear-gradient(90deg,#cdb99a,#1d2940)}.header{display:flex;justify-content:space-between;align-items:center;padding-bottom:18px;border-bottom:1px solid #dfe4ea}.brand{display:flex;align-items:center;gap:16px}.brand img{width:105px;max-height:70px;object-fit:contain}.brand-name{font-size:18px;font-weight:900}.brand-meta{font-size:11px;color:#7a8492;margin-top:4px}.document{text-align:end}.document h1{margin:0;font-size:25px;color:#19243a}.document p{margin:5px 0 0;color:#7a8492;font-size:11px}.party{margin:20px 0;background:linear-gradient(135deg,#172139,#283750);color:#fff;border-radius:18px;padding:18px 20px;display:flex;justify-content:space-between;gap:15px}.party h2{margin:0 0 4px;font-size:21px}.party p{margin:0;color:#cad2df;font-size:12px}.period{align-self:center;text-align:end;font-size:11px;color:#cad2df}.period strong{display:block;color:#fff;margin-top:4px}.summary{display:grid;grid-template-columns:repeat(4,1fr);gap:9px;margin-bottom:18px}.metric{border:1px solid #e3e7ed;border-radius:13px;padding:12px;background:#fafbfc}.metric span{display:block;color:#788394;font-size:10px}.metric strong{display:block;margin-top:6px;font-size:16px}.green{color:#13804b}.red{color:#bd3443}.details{display:grid;grid-template-columns:1.2fr .8fr;gap:10px;margin-bottom:19px}.panel{border:1px solid #e3e7ed;border-radius:13px;padding:12px}.panel-title{font-size:11px;color:#8b6f4e;font-weight:800;margin-bottom:8px}.contact{display:grid;grid-template-columns:repeat(2,1fr);gap:7px;font-size:11px}.contact span{color:#7d8795}.notes{font-size:11px;line-height:1.65;white-space:pre-wrap}.section-title{font-size:15px;font-weight:900;margin:0 0 9px}.table-wrap{border:1px solid #dde2e9;border-radius:12px;overflow:hidden}table{width:100%;border-collapse:collapse;font-size:9.5px}th{background:#f0ece5;color:#574a3b;padding:8px 6px;text-align:start}td{padding:7px 6px;border-top:1px solid #e7eaf0;vertical-align:top}tbody tr:nth-child(even){background:#fafbfc}.num{white-space:nowrap;font-variant-numeric:tabular-nums}.movement-notes{max-width:150px;line-height:1.5}tfoot td{font-weight:900;background:#f7f8fa;border-top:2px solid #d7dde5}.empty{text-align:center;padding:25px;color:#7d8795}.signatures{display:grid;grid-template-columns:1fr 1fr;gap:70px;margin-top:35px;text-align:center;font-size:11px;font-weight:700}.signature-line{border-top:1px solid #9ca5b2;padding-top:7px}.footer{margin-top:25px;padding-top:10px;border-top:1px solid #e1e5ea;display:flex;justify-content:space-between;color:#8a94a2;font-size:9px}.print-actions{text-align:center;margin:20px}.print-actions button{border:0;border-radius:12px;padding:13px 30px;background:#1d2940;color:#fff;font:700 14px Tajawal;cursor:pointer}
@media print{body{background:#fff}.sheet{width:auto;min-height:auto;margin:0;padding:5mm;box-shadow:none}.print-actions{display:none}.table-wrap{overflow:visible}thead{display:table-header-group}tr{break-inside:avoid}.signatures{break-inside:avoid}}
@media(max-width:800px){.sheet{width:100%;margin:0;padding:20px}.summary{grid-template-columns:repeat(2,1fr)}}
</style>
<style>.summary{grid-template-columns:repeat(5,1fr)}</style>
</head>
<body>
@php
    $logo = $setting?->company_logo ? asset('storage/'.$setting->company_logo) : ($company?->logo ? asset('storage/'.$company->logo) : asset('logo.png'));
    $companyName = $setting?->company_name ?: ($company?->name ?: 'Al Waleed');
    $currency = $setting?->currency ?: 'IQD';
@endphp
<main class="sheet">
    <header class="header">
        <div class="brand"><img src="{{ $logo }}" alt="{{ $companyName }}"><div><div class="brand-name">{{ $companyName }}</div><div class="brand-meta">{{ $setting?->phone ?: $company?->phone }} @if($setting?->email || $company?->email) · {{ $setting?->email ?: $company?->email }} @endif</div></div></div>
        <div class="document"><h1>{{ $statementTitle }}</h1><p>{{ __('messages.generated_at', ['date' => now()->format('Y/m/d H:i')]) }}</p></div>
    </header>
    <section class="party">
        <div><h2>{{ $party->name }}</h2><p>{{ $party->company_name ?: __('messages.statement_subtitle') }}</p></div>
        <div class="period">{{ __('messages.statement_period') }}<strong>{{ $from || $to ? (($from ?: '…').' — '.($to ?: '…')) : __('messages.all_dates') }}</strong></div>
    </section>
    <section class="summary">
        <div class="metric"><span>{{ __('messages.total_received') }}</span><strong class="green">{{ number_format($totalReceived,2) }} {{ $currency }}</strong></div>
        <div class="metric"><span>{{ __('messages.total_paid') }}</span><strong class="red">{{ number_format($totalPaid,2) }} {{ $currency }}</strong></div>
        <div class="metric"><span>{{ __('messages.current_balance') }}</span><strong class="{{ $balance >= 0 ? 'green' : 'red' }}">{{ number_format($balance,2) }} {{ $currency }}</strong></div>
        <div class="metric"><span>{{ __('messages.total_invoiced') }}</span><strong>{{ number_format($totalInvoiced,2) }} {{ $currency }}</strong></div>
        <div class="metric"><span>{{ __('messages.movements_count') }}</span><strong>{{ $movementsCount }}</strong></div>
    </section>
    <section class="details">
        <div class="panel"><div class="panel-title">{{ __('messages.contact_information') }}</div><div class="contact"><div><span>{{ __('messages.phone') }}:</span> {{ $party->phone ?: '-' }}</div><div><span>{{ __('messages.party_company') }}:</span> {{ $party->company_name ?: '-' }}</div><div style="grid-column:1/-1"><span>{{ __('messages.address') }}:</span> {{ $party->address ?: '-' }}</div></div></div>
        <div class="panel"><div class="panel-title">{{ __('messages.general_notes') }}</div><div class="notes">{{ $party->notes ?: __('messages.no_notes') }}</div></div>
    </section>
    <h3 class="section-title">{{ __('messages.movement_history') }}</h3>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>{{ __('messages.date') }}</th><th>{{ __('messages.reference') }}</th><th>{{ __('messages.movement_type') }}</th><th>{{ __('messages.invoiced') }}</th><th>{{ __('messages.received') }}</th><th>{{ __('messages.paid') }}</th><th>{{ __('messages.running_balance') }}</th><th>{{ __('messages.notes') }}</th></tr></thead>
        <tbody>@forelse($movements as $index => $movement)<tr><td>{{ $index+1 }}</td><td class="num">{{ $movement->date }}</td><td>{{ $movement->number }}</td><td>{{ $movement->type }}</td><td class="num">{{ $movement->invoiced ? number_format($movement->invoiced,2) : '—' }}</td><td class="num green">{{ $movement->received ? number_format($movement->received,2) : '—' }}</td><td class="num red">{{ $movement->paid ? number_format($movement->paid,2) : '—' }}</td><td class="num">{{ number_format($movement->balance,2) }}</td><td class="movement-notes">{{ $movement->notes ?: '—' }}</td></tr>@empty<tr><td colspan="9" class="empty">{{ __('messages.no_movements') }}</td></tr>@endforelse</tbody>
        <tfoot><tr><td colspan="4">{{ __('messages.total') }}</td><td class="num">{{ number_format($totalInvoiced,2) }}</td><td class="green num">{{ number_format($totalReceived,2) }}</td><td class="red num">{{ number_format($totalPaid,2) }}</td><td class="num">{{ number_format($balance,2) }}</td><td></td></tr></tfoot>
    </table></div>
    <section class="signatures"><div class="signature-line">{{ __('messages.authorized_signature') }}</div><div class="signature-line">{{ __('messages.account_holder_signature') }}</div></section>
    <footer class="footer"><span>{{ $companyName }}</span><span>{{ $setting?->address ?: $company?->address }}</span><span>Al Waleed ERP</span></footer>
</main>
<div class="print-actions"><button onclick="window.print()">{{ __('messages.print_statement') }}</button></div>
</body></html>
