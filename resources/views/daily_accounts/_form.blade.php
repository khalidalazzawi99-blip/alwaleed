<div class="da-form-grid">
<label>التاريخ<input type="date" name="expense_date" required value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : now()->toDateString()) }}"></label>
<label>المبلغ المصروف<input type="number" name="amount" min="0.01" step="0.01" required value="{{ old('amount', $expense->amount ?? '') }}"></label>
<label>الجهة / الشخص<select name="party_id" required><option value="">اختر الجهة</option>@foreach($parties->where('is_active', true) as $party)<option value="{{ $party->id }}" @selected((string) old('party_id', $expense->party_id ?? '') === (string) $party->id)>{{ $party->name }}</option>@endforeach</select></label>
<label>العملة<select name="currency" required>@foreach(config('daily_accounts.currencies') as $code)<option value="{{ $code }}" @selected(old('currency', $expense->currency ?? 'IQD') === $code)>{{ $code }}</option>@endforeach</select></label>
<label class="da-wide">الملاحظات<textarea name="notes" maxlength="5000">{{ old('notes', $expense->notes ?? '') }}</textarea></label>
</div>
