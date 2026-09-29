@extends('layouts.app')

@section('content')
<div class="topbar"><h1 class="page-title">تعديل حركة {{ $party->name }}</h1></div>
<div class="card" style="max-width:760px;margin:auto">
    <form method="POST" action="{{ route('party-debt-transactions.update', $transaction) }}">
        @csrf @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <label>نوع الحركة<select name="type" required><option value="borrowing" @selected(old('type', $transaction->type)==='borrowing')>استدانة</option><option value="debt_payment" @selected(old('type', $transaction->type)==='debt_payment')>سداد ديون</option></select></label>
            <label>التاريخ<input type="date" name="transaction_date" required value="{{ old('transaction_date', $transaction->transaction_date->toDateString()) }}"></label>
            <label>المبلغ<input type="number" name="amount" min="0.01" step="0.01" required value="{{ old('amount', $transaction->amount) }}"></label>
            <label style="grid-column:1/-1">الملاحظات<textarea name="notes" rows="4" maxlength="1000">{{ old('notes', $transaction->notes) }}</textarea></label>
        </div>
        <div style="display:flex;gap:10px;margin-top:20px"><button type="submit">حفظ التعديل</button><a class="btn" href="/{{ $partyType }}s/{{ $party->id }}">إلغاء</a></div>
    </form>
</div>
@endsection
