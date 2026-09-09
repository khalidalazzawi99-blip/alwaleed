@props(['cashboxes', 'selected' => null, 'id' => 'voucherAccount'])
<label for="{{ $id }}" style="display:flex;align-items:center;gap:8px;font-weight:700;margin-bottom:12px">
    <x-finance-icon name="bank" /> {{ __('messages.cashbox_or_bank') }}
</label>
<select name="cashbox_id" id="{{ $id }}" required>
    @foreach(['cash' => __('messages.cash_accounts'), 'bank' => __('messages.banks')] as $type => $groupLabel)
        @if($cashboxes->where('account_type', $type)->isNotEmpty())
            <optgroup label="{{ $groupLabel }}">
                @foreach($cashboxes->where('account_type', $type) as $account)
                    <option value="{{ $account->id }}" @selected((int) old('cashbox_id', $selected ?? request('cashbox_id', $cashboxes->first()?->id)) === $account->id)>
                        {{ $account->name }}{{ $account->account_number ? ' · '.$account->account_number : '' }} — {{ number_format($account->balance, 2) }} {{ $companyCurrency }}
                    </option>
                @endforeach
            </optgroup>
        @endif
    @endforeach
</select>
