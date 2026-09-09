@extends('layouts.app')

@section('content')
@include('cashbox._styles')
@php
    $canManageCash = auth()->user()->company?->hasFeature('multiple_cashboxes');
    $pageUrl = $banksOnly ? route('banks.index') : url('/cashbox');
    $movements = $cashboxLogs->map(fn ($log) => (object) [
        'type' => $log->type === 'إيداع مباشر' ? __('messages.deposit') : __('messages.withdrawal'),
        'incoming' => $log->type === 'إيداع مباشر', 'reference' => $log->reference_no,
        'account' => $log->cashbox?->name ?? '-', 'party' => $log->person_name,
        'amount' => $log->amount, 'date' => $log->created_at->format('Y-m-d H:i'),
        'sort' => $log->created_at->format('Y-m-d H:i:s'), 'notes' => $log->notes,
    ])->concat($receipts->map(fn ($receipt) => (object) [
        'type' => __('messages.received'), 'incoming' => true, 'reference' => $receipt->receipt_no,
        'account' => $receipt->cashbox?->name ?? '-', 'party' => $receipt->party?->name ?? '-',
        'amount' => $receipt->amount, 'date' => $receipt->receipt_date,
        'sort' => $receipt->receipt_date.' '.$receipt->created_at->format('H:i:s'), 'notes' => $receipt->notes,
    ]))->concat($payments->map(fn ($payment) => (object) [
        'type' => __('messages.paid'), 'incoming' => false, 'reference' => $payment->payment_no,
        'account' => $payment->cashbox?->name ?? '-', 'party' => $payment->party?->name ?? '-',
        'amount' => $payment->amount, 'date' => $payment->payment_date,
        'sort' => $payment->payment_date.' '.$payment->created_at->format('H:i:s'), 'notes' => $payment->notes,
    ]))->sortByDesc('sort');
@endphp

<div class="finance-page">
    <div class="finance-hero">
        <div class="finance-heading">
            <span class="finance-icon-tile"><x-finance-icon :name="$banksOnly ? 'bank' : 'wallet'" /></span>
            <div>
                <h1>{{ __($banksOnly ? 'messages.banks' : 'messages.cashbox_and_banks') }}</h1>
                <p class="finance-subtitle">{{ __('messages.accounts_intro') }}</p>
            </div>
        </div>
        <a class="finance-button" href="{{ route('banks.index') }}#add-bank"><x-finance-icon name="plus" /> {{ __('messages.add_bank') }}</a>
    </div>

    @if(session('success'))<div class="finance-notice" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="finance-notice error" role="alert">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="finance-toolbar">
        <nav class="finance-tabs" aria-label="{{ __('messages.accounts_navigation') }}">
            <a href="/cashbox" @class(['active' => !$banksOnly]) @if(!$banksOnly) aria-current="page" @endif><x-finance-icon name="wallet" /> {{ __('messages.all_accounts') }}</a>
            <a href="{{ route('banks.index') }}" @class(['active' => $banksOnly]) @if($banksOnly) aria-current="page" @endif><x-finance-icon name="bank" /> {{ __('messages.banks') }}</a>
        </nav>
        <form method="GET" action="{{ $pageUrl }}" class="finance-filter">
            <label for="accountFilter"><x-finance-icon name="filter" /><span class="sr-only">{{ __('messages.choose_account') }}</span></label>
            <select id="accountFilter" name="cashbox_id" aria-label="{{ __('messages.choose_account') }}">
                <option value="">{{ __($banksOnly ? 'messages.all_banks' : 'messages.all_accounts') }}</option>
                @foreach(['cash' => __('messages.cash_accounts'), 'bank' => __('messages.banks')] as $type => $group)
                    @if($cashboxes->where('account_type', $type)->isNotEmpty())
                        <optgroup label="{{ $group }}">
                            @foreach($cashboxes->where('account_type', $type) as $account)
                                <option value="{{ $account->id }}" @selected($selectedId === $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
            <button class="finance-button secondary">{{ __('messages.show_account') }}</button>
        </form>
    </div>

    <div class="finance-summary">
        <article><span class="finance-icon-tile"><x-finance-icon name="wallet" /></span><div><span>{{ __('messages.accounts_balance') }}</span><strong><bdi>{{ number_format($balance, 2) }}</bdi> <small>{{ $companyCurrency }}</small></strong></div></article>
        <article><span class="finance-icon-tile finance-in"><x-finance-icon name="deposit" /></span><div><span>{{ __('messages.total_incoming') }}</span><strong class="finance-in"><bdi>{{ number_format($totalReceipts, 2) }}</bdi> <small>{{ $companyCurrency }}</small></strong></div></article>
        <article><span class="finance-icon-tile finance-out"><x-finance-icon name="withdrawal" /></span><div><span>{{ __('messages.total_outgoing') }}</span><strong class="finance-out"><bdi>{{ number_format($totalPayments, 2) }}</bdi> <small>{{ $companyCurrency }}</small></strong></div></article>
    </div>

    <section>
        <div class="finance-section-head">
            <h2>{{ __($banksOnly ? 'messages.bank_accounts' : 'messages.your_accounts') }}</h2>
            <span class="finance-count">{{ $visibleCashboxes->count() }} {{ __('messages.account_count_label') }}</span>
        </div>
        <div class="finance-accounts">
            @forelse($visibleCashboxes as $box)
                @php
                    $isBank = $box->account_type === 'bank';
                    $accountBase = $isBank ? '/banks/'.$box->id : '/cashbox/'.$box->id;
                    $transactionKey = 'transaction-'.$box->id;
                    $restoreTransaction = old('form_key') === $transactionKey;
                @endphp
                <article class="finance-account" id="account-{{ $box->id }}">
                    <header class="finance-account-head">
                        <div class="finance-account-title">
                            <span class="finance-icon-tile"><x-finance-icon :name="$isBank ? 'bank' : 'wallet'" /></span>
                            <div>
                                <h3>{{ $box->name }}</h3>
                                <p class="finance-subtitle">{{ __($isBank ? 'messages.bank_account' : 'messages.cash_account') }}@if($box->account_number) · <bdi>{{ $box->account_number }}</bdi>@endif</p>
                            </div>
                            <span @class(['finance-status', 'inactive' => !$box->is_active])>{{ __($box->is_active ? 'messages.account_active' : 'messages.account_inactive') }}</span>
                        </div>
                        <div class="finance-account-balance"><strong><bdi>{{ number_format($box->balance, 2) }}</bdi></strong><small>{{ $companyCurrency }}</small></div>
                    </header>

                    @if($box->is_active)
                        <form method="POST" action="{{ $accountBase }}/transactions" class="finance-transaction">
                            @csrf
                            <input type="hidden" name="form_key" value="{{ $transactionKey }}">
                            <fieldset class="finance-operation">
                                <legend>{{ __('messages.operation_type') }}</legend>
                                <label class="finance-choice"><input type="radio" name="type" value="deposit" @checked(!$restoreTransaction || old('type', 'deposit') === 'deposit') required><span><x-finance-icon name="deposit" /> {{ __('messages.deposit') }}</span></label>
                                <label class="finance-choice withdraw"><input type="radio" name="type" value="withdrawal" @checked($restoreTransaction && old('type') === 'withdrawal')><span><x-finance-icon name="withdrawal" /> {{ __('messages.withdrawal') }}</span></label>
                            </fieldset>
                            <div class="finance-field">
                                <label for="amount-{{ $box->id }}">{{ __('messages.amount') }} ({{ $companyCurrency }})</label>
                                <input id="amount-{{ $box->id }}" type="number" name="amount" min="0.01" step="0.01" placeholder="0.00" value="{{ $restoreTransaction ? old('amount') : '' }}" required>
                            </div>
                            @if($box->customers->isNotEmpty())
                                <div class="finance-field">
                                    <label for="customer-{{ $box->id }}">{{ __('messages.customer_optional') }}</label>
                                    <select id="customer-{{ $box->id }}" name="customer_id">
                                        <option value="">{{ __('messages.without_customer') }}</option>
                                        @foreach($box->customers as $customer)<option value="{{ $customer->id }}" @selected($restoreTransaction && (int) old('customer_id') === $customer->id)>{{ $customer->name }}</option>@endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="finance-field">
                                <label for="notes-{{ $box->id }}">{{ __('messages.notes_optional') }}</label>
                                <textarea id="notes-{{ $box->id }}" name="notes" rows="2" maxlength="1000" placeholder="{{ __('messages.transaction_note_hint') }}">{{ $restoreTransaction ? old('notes') : '' }}</textarea>
                            </div>
                            <button type="submit" class="finance-button finance-submit"><x-finance-icon name="plus" /> {{ __('messages.save_transaction') }}</button>
                        </form>
                        <div class="finance-account-links">
                            <a href="{{ url('/receipts').'?cashbox_id='.$box->id }}"><x-finance-icon name="receipt" /> {{ __('messages.new_receipt_here') }}</a>
                            <a href="{{ $pageUrl.'?cashbox_id='.$box->id }}#account-history"><x-finance-icon name="history" /> {{ __('messages.account_movements') }}</a>
                        </div>
                    @else
                        <p class="finance-subtitle" style="padding:20px">{{ __('messages.inactive_account_hint') }}</p>
                    @endif
                    @if($isBank || $canManageCash)
                        <details class="finance-edit" @if(old('form_key') === 'edit-'.$box->id) open @endif>
                            <summary><x-finance-icon name="edit" /> {{ __('messages.edit_account') }}</summary>
                            <form method="POST" action="{{ $accountBase }}">
                                @csrf @method('PUT')
                                @include('cashbox._account-fields', ['accountType' => $isBank ? 'bank' : 'cash', 'editingBox' => $box])
                            </form>
                            @if(!$isBank && $cashboxes->count() > 1 && (float) $box->balance === 0.0)
                                <form method="POST" action="{{ $accountBase }}">@csrf @method('DELETE')<button class="finance-button secondary">{{ __('messages.delete') }}</button></form>
                            @endif
                        </details>
                    @endif
                </article>
            @empty
                <div class="finance-empty">
                    <span class="finance-icon-tile"><x-finance-icon :name="$banksOnly ? 'bank' : 'wallet'" /></span>
                    <p>{{ __($banksOnly ? 'messages.no_banks' : 'messages.no_accounts') }}</p>
                    <a class="finance-button" href="{{ route('banks.index') }}#add-bank"><x-finance-icon name="plus" /> {{ __('messages.add_bank') }}</a>
                </div>
            @endforelse
        </div>
    </section>

    @if($banksOnly)
        <details class="finance-create" id="add-bank" @if($cashboxes->isEmpty() || old('form_key') === 'create-bank') open @endif>
            <summary><x-finance-icon name="bank" /> {{ __('messages.add_bank') }}</summary>
            <form method="POST" action="{{ route('banks.store') }}">
                @csrf
                @include('cashbox._account-fields', ['accountType' => 'bank', 'editingBox' => null])
            </form>
        </details>
    @elseif($canManageCash)
        <details class="finance-create" id="add-cashbox" @if(old('form_key') === 'create-cash') open @endif>
            <summary><x-finance-icon name="plus" /> {{ __('messages.add_cashbox') }}</summary>
            <form method="POST" action="/cashbox">
                @csrf
                @include('cashbox._account-fields', ['accountType' => 'cash', 'editingBox' => null])
            </form>
        </details>
    @endif

    <section class="finance-history" id="account-history">
        <div class="finance-section-head"><h2><x-finance-icon name="history" /> {{ __('messages.account_movements') }}</h2><span class="finance-count">{{ $movements->count() }} {{ __('messages.movements_label') }}</span></div>
        <div class="finance-table-wrap">
            <table>
                <thead><tr><th>{{ __('messages.movement_type') }}</th><th>{{ __('messages.reference') }}</th><th>{{ __('messages.cashbox_or_bank') }}</th><th>{{ __('messages.party') }}</th><th>{{ __('messages.amount') }}</th><th>{{ __('messages.date') }}</th><th>{{ __('messages.notes') }}</th></tr></thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td @class(['finance-in' => $movement->incoming, 'finance-out' => !$movement->incoming])>{{ $movement->type }}</td>
                            <td><bdi>{{ $movement->reference }}</bdi></td><td>{{ $movement->account }}</td><td>{{ $movement->party }}</td>
                            <td @class(['finance-in' => $movement->incoming, 'finance-out' => !$movement->incoming])><bdi>{{ $movement->incoming ? '+' : '−' }}{{ number_format($movement->amount, 2) }}</bdi> {{ $companyCurrency }}</td>
                            <td><bdi>{{ $movement->date }}</bdi></td><td>{{ $movement->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;padding:30px">{{ __('messages.no_account_movements') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
<script>
function openAccountSection() {
    const section = document.getElementById(window.location.hash.slice(1));
    if (section instanceof HTMLDetailsElement) {
        section.open = true;
        section.scrollIntoView({block: 'start'});
    }
}
window.addEventListener('hashchange', openAccountSection);
openAccountSection();
document.querySelectorAll('.finance-transaction').forEach(form => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
    });
});
</script>
@endsection
