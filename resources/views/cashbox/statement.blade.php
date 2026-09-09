@extends('layouts.app')
@section('content')
@include('cashbox._styles')
<div class="finance-page">
    <header class="finance-hero">
        <div><h1>{{ __('messages.account_statement') }}</h1><p class="finance-subtitle">{{ __('messages.account_statement_intro') }}</p></div>
        <div class="finance-actions">
            <a href="{{ $cashbox->account_type === 'bank' ? route('banks.index') : url('/cashbox') }}" class="finance-button secondary">{{ __('messages.back_to_accounts') }}</a>
            <a href="{{ route($statementRoute.'.print', ['cashbox' => $cashbox->id, 'from' => $from, 'to' => $to]) }}" class="finance-button" target="_blank" rel="noopener"><x-finance-icon name="print" /> {{ __('messages.print_statement') }}</a>
        </div>
    </header>
    <div class="finance-statement-identity">
        <div class="finance-heading"><span class="finance-icon-tile"><x-finance-icon :name="$cashbox->account_type === 'bank' ? 'bank' : 'wallet'" /></span><div><h2>{{ $cashbox->name }}</h2><p class="finance-subtitle">{{ __($cashbox->account_type === 'bank' ? 'messages.bank_account' : 'messages.cash_account') }}@if($cashbox->account_number) · <bdi>{{ $cashbox->account_number }}</bdi>@endif</p></div></div>
        <div class="finance-statement-current">{{ __('messages.accounts_balance') }}<strong><bdi>{{ number_format($cashbox->balance, 2) }}</bdi> {{ $companyCurrency }}</strong></div>
    </div>
    @if($errors->any())<div class="finance-notice error" role="alert">{{ $errors->first() }}</div>@endif
    <form method="GET" action="{{ route($statementRoute, $cashbox) }}" class="finance-period-form">
        <div class="finance-field"><label for="statementFrom">{{ __('messages.statement_from') }}</label><input id="statementFrom" type="date" name="from" value="{{ old('from', $from) }}"></div>
        <div class="finance-field"><label for="statementTo">{{ __('messages.statement_to') }}</label><input id="statementTo" type="date" name="to" value="{{ old('to', $to) }}"></div>
        <button class="finance-button"><x-finance-icon name="filter" /> {{ __('messages.show_statement') }}</button>
        <a href="{{ route($statementRoute, $cashbox) }}" class="finance-button secondary">{{ __('messages.all_dates') }}</a>
    </form>
    <div class="finance-summary finance-statement-summary">
        <article><span>{{ __('messages.period_opening_balance') }}</span><strong><bdi>{{ number_format($openingBalance, 2) }}</bdi> <small>{{ $companyCurrency }}</small></strong></article>
        <article><span>{{ __('messages.statement_incoming') }}</span><strong class="finance-in"><bdi>{{ number_format($totalIncoming, 2) }}</bdi> <small>{{ $companyCurrency }}</small></strong></article>
        <article><span>{{ __('messages.statement_outgoing') }}</span><strong class="finance-out"><bdi>{{ number_format($totalOutgoing, 2) }}</bdi> <small>{{ $companyCurrency }}</small></strong></article>
        <article><span>{{ __('messages.period_closing_balance') }}</span><strong><bdi>{{ number_format($closingBalance, 2) }}</bdi> <small>{{ $companyCurrency }}</small></strong></article>
    </div>
    <section class="finance-statement-panel">
        <div class="finance-section-head"><div><h2>{{ __('messages.statement_transactions') }}</h2><p class="finance-subtitle">{{ $from ?: __('messages.all_dates') }} @if($to) — {{ $to }} @endif · {{ $companyCurrency }}</p></div><span class="finance-count">{{ $movements->count() }} {{ __('messages.movements_label') }}</span></div>
        <div class="finance-table-wrap">@include('cashbox._statement-table')</div>
    </section>
    <p class="finance-statement-footnote">{{ __('messages.statement_effective_note') }}</p>
</div>
@endsection
