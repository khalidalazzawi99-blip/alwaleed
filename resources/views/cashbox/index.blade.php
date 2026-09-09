@extends('layouts.app')

@section('content')
@include('cashbox._styles')
@php
    $canManageCash = auth()->user()->company?->hasFeature('multiple_cashboxes');
    $pageUrl = $banksOnly ? route('banks.index') : url('/cashbox');
@endphp
<div class="finance-page">
    <header class="finance-hero">
        <div class="finance-heading">
            <span class="finance-icon-tile"><x-finance-icon :name="$banksOnly ? 'bank' : 'wallet'" /></span>
            <div><h1>{{ __($banksOnly ? 'messages.banks' : 'messages.cashbox_and_banks') }}</h1><p class="finance-subtitle">{{ __('messages.accounts_simple_intro') }}</p></div>
        </div>
        <div class="finance-actions">
            @if(!$banksOnly && $canManageCash)<button type="button" class="finance-button secondary" data-dialog="create-cash"><x-finance-icon name="plus" /> {{ __('messages.add_cashbox') }}</button>@endif
            <button type="button" class="finance-button" data-dialog="create-bank"><x-finance-icon name="plus" /> {{ __('messages.add_bank') }}</button>
        </div>
    </header>

    @if(session('success'))<div class="finance-notice" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="finance-notice error" role="alert">{{ $errors->first() }}</div>@endif

    <div class="finance-toolbar">
        <nav class="finance-tabs" aria-label="{{ __('messages.accounts_navigation') }}">
            <a href="/cashbox" @class(['active' => !$banksOnly]) @if(!$banksOnly) aria-current="page" @endif><x-finance-icon name="wallet" /> {{ __('messages.all_accounts') }}</a>
            <a href="{{ route('banks.index') }}" @class(['active' => $banksOnly]) @if($banksOnly) aria-current="page" @endif><x-finance-icon name="bank" /> {{ __('messages.banks') }}</a>
        </nav>
        <form method="GET" action="{{ $pageUrl }}" class="finance-filter">
            <label for="accountFilter" class="finance-sr-only">{{ __('messages.choose_account') }}</label>
            <select id="accountFilter" name="cashbox_id">
                <option value="">{{ __($banksOnly ? 'messages.all_banks' : 'messages.all_accounts') }}</option>
                @foreach($cashboxes as $account)<option value="{{ $account->id }}" @selected($selectedId === $account->id)>{{ $account->name }}</option>@endforeach
            </select>
            <button class="finance-button secondary"><x-finance-icon name="filter" /> {{ __('messages.show_account') }}</button>
        </form>
    </div>

    <div class="finance-summary">
        <article><span>{{ __('messages.accounts_balance') }}</span><strong><bdi>{{ number_format($balance, 2) }}</bdi> <small>{{ $companyCurrency }}</small></strong></article>
        <article><span>{{ __('messages.total_incoming') }}</span><strong class="finance-in"><bdi>{{ number_format($totalReceipts, 2) }}</bdi> <small>{{ $companyCurrency }}</small></strong></article>
        <article><span>{{ __('messages.total_outgoing') }}</span><strong class="finance-out"><bdi>{{ number_format($totalPayments, 2) }}</bdi> <small>{{ $companyCurrency }}</small></strong></article>
    </div>

    <section class="finance-list">
        <div class="finance-section-head"><h2>{{ __($banksOnly ? 'messages.bank_accounts' : 'messages.your_accounts') }}</h2><span class="finance-count">{{ $visibleCashboxes->count() }} {{ __('messages.account_count_label') }}</span></div>
        <div class="finance-list-head" aria-hidden="true"><span>{{ __('messages.account_details') }}</span><span>{{ __('messages.accounts_balance') }}</span><span>{{ __('messages.account_actions') }}</span></div>
        @forelse($visibleCashboxes as $box)
            @php $isBank = $box->account_type === 'bank'; @endphp
            <article class="finance-row" id="account-{{ $box->id }}">
                <div class="finance-account-title">
                    <span class="finance-icon-tile"><x-finance-icon :name="$isBank ? 'bank' : 'wallet'" /></span>
                    <div><h3>{{ $box->name }}</h3><p class="finance-subtitle">{{ __($isBank ? 'messages.bank_account' : 'messages.cash_account') }}@if($box->account_number) · <bdi>{{ $box->account_number }}</bdi>@endif</p><span @class(['finance-status', 'inactive' => !$box->is_active])>{{ __($box->is_active ? 'messages.account_active' : 'messages.account_inactive') }}</span></div>
                </div>
                <div class="finance-row-balance"><strong><bdi>{{ number_format($box->balance, 2) }}</bdi></strong><small>{{ $companyCurrency }}</small></div>
                <div class="finance-actions">
                    @if($box->is_active)
                        <button type="button" class="finance-button secondary deposit-action" data-dialog="transaction-{{ $box->id }}" data-operation="deposit"><x-finance-icon name="deposit" /> {{ __('messages.deposit') }}</button>
                        <button type="button" class="finance-button secondary withdraw-action" data-dialog="transaction-{{ $box->id }}" data-operation="withdrawal"><x-finance-icon name="withdrawal" /> {{ __('messages.withdrawal') }}</button>
                    @endif
                    <a class="finance-button" href="{{ route($isBank ? 'banks.statement' : 'cashbox.statement', $box) }}"><x-finance-icon name="history" /> {{ __('messages.account_statement') }}</a>
                    @if($isBank || $canManageCash)<button type="button" class="finance-button secondary icon-only" data-dialog="edit-{{ $box->id }}" aria-label="{{ __('messages.edit_account') }} — {{ $box->name }}" title="{{ __('messages.edit_account') }}"><x-finance-icon name="edit" /></button>@endif
                </div>
            </article>
        @empty
            <div class="finance-empty"><span class="finance-icon-tile"><x-finance-icon name="bank" /></span><p>{{ __($banksOnly ? 'messages.no_banks' : 'messages.no_accounts') }}</p><button type="button" class="finance-button" data-dialog="create-bank"><x-finance-icon name="plus" /> {{ __('messages.add_bank') }}</button></div>
        @endforelse
    </section>

    @foreach($visibleCashboxes as $box)
        @php
            $isBank = $box->account_type === 'bank';
            $accountBase = $isBank ? '/banks/'.$box->id : '/cashbox/'.$box->id;
            $transactionKey = 'transaction-'.$box->id;
            $restoreTransaction = old('form_key') === $transactionKey;
        @endphp
        @if($box->is_active)
            <dialog class="finance-dialog" id="{{ $transactionKey }}" aria-labelledby="{{ $transactionKey }}-title" @if($restoreTransaction) data-auto-open @endif>
                <div class="finance-dialog-head"><div><h2 id="{{ $transactionKey }}-title" data-operation-title>{{ __('messages.deposit') }}</h2><p class="finance-subtitle">{{ $box->name }} · <bdi>{{ number_format($box->balance, 2) }}</bdi> {{ $companyCurrency }}</p></div><button type="button" class="finance-close" data-close aria-label="{{ __('messages.close_dialog') }}">×</button></div>
                <form method="POST" action="{{ $accountBase }}/transactions" class="finance-transaction">
                    @csrf
                    <input type="hidden" name="form_key" value="{{ $transactionKey }}">
                    @if($restoreTransaction && $errors->any())<div class="finance-notice error" role="alert">{{ $errors->first() }}</div>@endif
                    <fieldset class="finance-operation">
                        <legend class="finance-sr-only">{{ __('messages.operation_type') }}</legend>
                        <label class="finance-choice"><input type="radio" name="type" value="deposit" @checked(!$restoreTransaction || old('type', 'deposit') === 'deposit') required><span><x-finance-icon name="deposit" /> {{ __('messages.deposit') }}</span></label>
                        <label class="finance-choice withdraw"><input type="radio" name="type" value="withdrawal" @checked($restoreTransaction && old('type') === 'withdrawal')><span><x-finance-icon name="withdrawal" /> {{ __('messages.withdrawal') }}</span></label>
                    </fieldset>
                    <div class="finance-field"><label for="amount-{{ $box->id }}">{{ __('messages.amount') }} ({{ $companyCurrency }})</label><input id="amount-{{ $box->id }}" type="number" name="amount" min="0.01" step="0.01" placeholder="0.00" value="{{ $restoreTransaction ? old('amount') : '' }}" required></div>
                    @if($box->customers->isNotEmpty())
                        <div class="finance-field"><label for="customer-{{ $box->id }}">{{ __('messages.customer_optional') }}</label><select id="customer-{{ $box->id }}" name="customer_id"><option value="">{{ __('messages.without_customer') }}</option>@foreach($box->customers as $customer)<option value="{{ $customer->id }}" @selected($restoreTransaction && (int) old('customer_id') === $customer->id)>{{ $customer->name }}</option>@endforeach</select></div>
                    @endif
                    <div class="finance-field"><label for="notes-{{ $box->id }}">{{ __('messages.notes_optional') }}</label><textarea id="notes-{{ $box->id }}" name="notes" rows="2" maxlength="1000" placeholder="{{ __('messages.transaction_note_hint') }}">{{ $restoreTransaction ? old('notes') : '' }}</textarea></div>
                    <div class="finance-dialog-footer"><button type="button" class="finance-button secondary" data-close>{{ __('messages.cancel_action') }}</button><button type="submit" class="finance-button finance-submit" data-operation-submit>{{ __('messages.deposit') }}</button></div>
                    <a class="finance-text-link" href="{{ url('/receipts').'?cashbox_id='.$box->id }}"><x-finance-icon name="receipt" /> {{ __('messages.new_receipt_here') }}</a>
                </form>
            </dialog>
        @endif
        @if($isBank || $canManageCash)
            <dialog class="finance-dialog" id="edit-{{ $box->id }}" aria-labelledby="edit-{{ $box->id }}-title" @if(old('form_key') === 'edit-'.$box->id) data-auto-open @endif>
                <div class="finance-dialog-head"><h2 id="edit-{{ $box->id }}-title">{{ __('messages.edit_account') }}</h2><button type="button" class="finance-close" data-close aria-label="{{ __('messages.close_dialog') }}">×</button></div>
                <form method="POST" action="{{ $accountBase }}" class="finance-dialog-body">
                    @csrf @method('PUT')
                    @if(old('form_key') === 'edit-'.$box->id && $errors->any())<div class="finance-notice error" role="alert">{{ $errors->first() }}</div>@endif
                    @include('cashbox._account-fields', ['accountType' => $isBank ? 'bank' : 'cash', 'editingBox' => $box])
                </form>
                @if(!$isBank && $cashboxes->count()>1 && (float)$box->balance===0.0)<form method="POST" action="{{ $accountBase }}" class="finance-dialog-body">@csrf @method('DELETE')<button class="finance-button secondary">{{ __('messages.delete') }}</button></form>@endif
            </dialog>
        @endif
    @endforeach

    @foreach(['bank', 'cash'] as $accountType)
        @if($accountType === 'bank' || (!$banksOnly && $canManageCash))
            <dialog class="finance-dialog" id="create-{{ $accountType }}" aria-labelledby="create-{{ $accountType }}-title" @if(old('form_key') === 'create-'.$accountType) data-auto-open @endif>
                <div class="finance-dialog-head"><h2 id="create-{{ $accountType }}-title">{{ __($accountType === 'bank' ? 'messages.add_bank' : 'messages.add_cashbox') }}</h2><button type="button" class="finance-close" data-close aria-label="{{ __('messages.close_dialog') }}">×</button></div>
                <form method="POST" action="{{ $accountType === 'bank' ? route('banks.store') : url('/cashbox') }}" class="finance-dialog-body">
                    @csrf
                    @if(old('form_key') === 'create-'.$accountType && $errors->any())<div class="finance-notice error" role="alert">{{ $errors->first() }}</div>@endif
                    @include('cashbox._account-fields', ['accountType' => $accountType, 'editingBox' => null])
                </form>
            </dialog>
        @endif
    @endforeach
</div>
<script>
(() => {
    const operationLabels = {deposit: @json(__('messages.deposit')), withdrawal: @json(__('messages.withdrawal'))};
    function updateOperation(dialog) {
        const type = dialog.querySelector('input[name="type"]:checked')?.value;
        if (!type) return;
        dialog.querySelector('[data-operation-title]').textContent = operationLabels[type];
        dialog.querySelector('[data-operation-submit]').textContent = operationLabels[type];
    }
    document.querySelectorAll('[data-dialog]').forEach(button => button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.dialog);
        if (!dialog) return;
        if (button.dataset.operation) {
            dialog.querySelectorAll('input[name="type"]').forEach(input => input.checked = input.value === button.dataset.operation);
        }
        updateOperation(dialog);
        dialog.showModal();
        dialog.querySelector('input[name="amount"]')?.focus();
    }));
    document.querySelectorAll('.finance-dialog').forEach(dialog => {
        dialog.querySelectorAll('[data-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
        dialog.querySelectorAll('input[name="type"]').forEach(input => input.addEventListener('change', () => updateOperation(dialog)));
        if (dialog.hasAttribute('data-auto-open')) { updateOperation(dialog); dialog.showModal(); }
        dialog.querySelectorAll('form').forEach(form => form.addEventListener('submit', () => {
            form.querySelectorAll('button[type="submit"]').forEach(button => button.disabled = true);
        }));
    });
    function openLinkedDialog() {
        const dialogId = {'#add-bank': 'create-bank', '#add-cashbox': 'create-cash'}[location.hash];
        const dialog = document.getElementById(dialogId);
        if (dialog && !dialog.open) dialog.showModal();
    }
    window.addEventListener('hashchange', openLinkedDialog);
    window.addEventListener('pageshow', () => document.querySelectorAll('.finance-dialog button[type="submit"]').forEach(button => button.disabled = false));
    openLinkedDialog();
})();
</script>
@endsection
