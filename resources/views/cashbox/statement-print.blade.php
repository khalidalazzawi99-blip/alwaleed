<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ __('messages.account_statement') }} — {{ $cashbox->name }}</title>
@include('documents._styles')
<style>
@page{size:A4 landscape;margin:12mm}
.document{width:calc(100% - 40px);max-width:1100px;min-height:0;margin:20px auto;padding:24px}.top-gradient{margin:-24px -24px 20px}
.bank-print-heading{display:flex;justify-content:space-between;gap:20px;margin:22px 0;border-bottom:1px solid #ddd;padding-bottom:16px}
.bank-print-heading h2{margin:0 0 6px;font-size:22px}.bank-print-heading p{margin:4px 0;color:#657083;font-size:12px}
.bank-print-summary{width:100%;border-collapse:collapse;margin:20px 0}.bank-print-summary td{border:1px solid #ddd;padding:12px;width:25%;font-size:12px}.bank-print-summary strong{display:block;margin-top:7px;font-size:20px}
.finance-statement-table{width:100%;border-collapse:collapse;font-size:11px;table-layout:fixed}
.finance-statement-table th,.finance-statement-table td{border:1px solid #ddd;padding:9px 7px;text-align:start;vertical-align:top;overflow-wrap:anywhere}
.finance-statement-table th{background:#eee9e1;font-weight:800}.finance-statement-table th:first-child{width:4%}.finance-statement-table th:nth-child(2){width:11%}.finance-statement-table th:nth-child(3){width:23%}.finance-statement-table th:nth-child(4){width:20%}
.finance-statement-table .numeric{text-align:end;white-space:nowrap;font-variant-numeric:tabular-nums}
.statement-description strong,.statement-description small{display:block}.statement-description small{margin-top:4px;line-height:1.6;color:#657083}
.statement-reference{font-size:9px}.statement-opening,.finance-statement-table tfoot{background:#f7f4ee;font-weight:700}
.finance-in{color:#13714e}.finance-out{color:#a6323e}.balance-cell{font-weight:800}
.finance-statement-table thead{display:table-header-group}.finance-statement-table tfoot{display:table-row-group}.finance-statement-table tr{break-inside:avoid}
.print-note{font-size:10px;color:#657083;margin:16px 0}
@media print{.document{margin:0;padding:0!important;max-width:none;box-shadow:none;border:0}.top-gradient{margin:0 0 20px}.actions{display:none!important}body{background:white}}
</style></head>
<body><main class="document">
@include('documents._header', ['companyId' => $cashbox->company_id, 'documentTitle' => __('messages.account_statement')])
<div class="bank-print-heading"><div><h2>{{ $cashbox->name }}</h2><p>{{ __($cashbox->account_type === 'bank' ? 'messages.bank_account' : 'messages.cash_account') }}@if($cashbox->account_number) · <bdi>{{ $cashbox->account_number }}</bdi>@endif</p></div><div><p>{{ __('messages.statement_period') }}</p><strong><bdi>{{ $from ?: __('messages.all_dates') }}</bdi>@if($to) — <bdi>{{ $to }}</bdi>@endif</strong><p>{{ $companyCurrency }}</p></div></div>
<table class="bank-print-summary"><tr><td>{{ __('messages.period_opening_balance') }}<strong>{{ number_format($openingBalance, 2) }}</strong></td><td>{{ __('messages.statement_incoming') }}<strong class="finance-in">{{ number_format($totalIncoming, 2) }}</strong></td><td>{{ __('messages.statement_outgoing') }}<strong class="finance-out">{{ number_format($totalOutgoing, 2) }}</strong></td><td>{{ __('messages.period_closing_balance') }}<strong>{{ number_format($closingBalance, 2) }}</strong></td></tr></table>
@include('cashbox._statement-table')
<p class="print-note">{{ __('messages.statement_effective_note') }}</p>
@include('documents._footer', ['companyId' => $cashbox->company_id])
</main><div class="actions"><button onclick="window.print()">{{ __('messages.print_statement') }}</button></div></body></html>
