@php
    $editing = isset($editingBox);
    $fieldKey = $editing ? 'edit-'.$editingBox->id : 'create-'.$accountType;
    $restore = old('form_key') === $fieldKey;
    $fieldValue = fn ($name, $default = '') => $restore ? old($name, $default) : ($editing ? ($editingBox->{$name} ?? $default) : $default);
@endphp
<input type="hidden" name="form_key" value="{{ $fieldKey }}">
<input type="hidden" name="account_type" value="{{ $accountType }}">
<div class="finance-form-grid">
    <div class="finance-field">
        <label for="{{ $fieldKey }}-name">{{ __($accountType === 'bank' ? 'messages.bank_name' : 'messages.cashbox_name') }}</label>
        <input id="{{ $fieldKey }}-name" name="name" value="{{ $fieldValue('name') }}" required maxlength="255">
    </div>
    @if($accountType === 'bank')
        <div class="finance-field">
            <label for="{{ $fieldKey }}-number">{{ __('messages.account_number_optional') }}</label>
            <input id="{{ $fieldKey }}-number" name="account_number" value="{{ $fieldValue('account_number') }}" maxlength="100" dir="ltr">
        </div>
    @endif
    @unless($editing)
        <div class="finance-field">
            <label for="{{ $fieldKey }}-balance">{{ __('messages.opening_balance') }} ({{ $companyCurrency }})</label>
            <input id="{{ $fieldKey }}-balance" type="number" name="balance" value="{{ $fieldValue('balance', 0) }}" min="0" step="0.01">
        </div>
    @endunless
    <div class="finance-field">
        <label for="{{ $fieldKey }}-customers">{{ __('messages.linked_customers_optional') }}</label>
        <select id="{{ $fieldKey }}-customers" name="customer_ids[]" multiple size="3">
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected($restore ? in_array($customer->id, old('customer_ids', [])) : ($editing && $editingBox->customers->contains($customer->id)))>{{ $customer->name }}</option>
            @endforeach
        </select>
    </div>
</div>
@if($editing)
    <label class="finance-check"><input type="checkbox" name="is_active" value="1" @checked($restore ? old('is_active') : $editingBox->is_active)> {{ __('messages.account_active') }}</label>
@endif
<button class="finance-button" type="submit"><x-finance-icon :name="$editing ? 'edit' : 'plus'" /> {{ __($editing ? 'messages.save_account' : ($accountType === 'bank' ? 'messages.add_bank' : 'messages.add_cashbox')) }}</button>
