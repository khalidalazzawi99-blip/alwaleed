@extends('layouts.app')

@section('content')
@php
    $meta = config('features.modules.'.$module);
    $labels = [
        'inventory' => ['title'=>'المخزون','name'=>'اسم المادة أو الصنف','amount'=>'القيمة'],
        'sales' => ['title'=>'المبيعات','name'=>'الزبون أو وصف الفاتورة','amount'=>'قيمة البيع'],
        'purchases' => ['title'=>'المشتريات','name'=>'المورد أو وصف الفاتورة','amount'=>'قيمة الشراء'],
        'payroll' => ['title'=>'الرواتب','name'=>'اسم الموظف','amount'=>'صافي الراتب'],
        'projects' => ['title'=>'إدارة المشاريع','name'=>'اسم المشروع','amount'=>'الميزانية'],
        'installments' => ['title'=>'الأقساط','name'=>'اسم العميل أو العقد','amount'=>'قيمة القسط'],
    ][$module];
@endphp
<div class="topbar"><div><h1 class="page-title">{{ $meta['icon'] }} {{ $labels['title'] }}</h1><p style="color:#8A8178">{{ __($meta['description']) }}</p></div></div>
@if(session('success'))<div class="card" style="color:#166534;background:#ECFDF3">{{ session('success') }}</div>@endif
@if($errors->any())<div class="card" style="color:#B91C1C">{{ $errors->first() }}</div>@endif
<div class="card">
    <h2>إضافة سجل جديد</h2>
    <form method="POST" action="/modules/{{ $module }}" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px" data-installment-form>@csrf
        <div><label>الرقم المرجعي</label><input name="reference" value="{{ old('reference') }}"></div>
        <div><label>{{ $labels['name'] }}</label><input name="name" value="{{ old('name') }}" required></div>
        @if($module === 'installments')
            <div><label>المبلغ المدفوع</label><input type="number" step="0.01" min="0" name="paid_amount" value="{{ old('paid_amount', 0) }}" data-paid-amount></div>
            <div><label>المبلغ الباقي</label><input type="number" step="0.01" value="{{ max(0, (float) old('amount', 0) - (float) old('paid_amount', 0)) }}" data-remaining-amount readonly></div>
        @endif
        <div><label>التاريخ</label><input type="date" name="record_date" value="{{ old('record_date', now()->toDateString()) }}" required></div>
        <div><label>{{ $labels['amount'] }}</label><input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', 0) }}" @if($module === 'installments') data-total-amount @endif></div>
        <div><label>الحالة</label><select name="status"><option value="active">فعال</option><option value="pending">قيد الانتظار</option><option value="completed">مكتمل</option><option value="cancelled">ملغي</option></select></div>
        <div><label>ملاحظات</label><input name="notes" value="{{ old('notes') }}"></div>
        <div><button type="submit">إضافة السجل</button></div>
    </form>
</div>
<div class="card"><h2>السجلات</h2><div style="overflow:auto"><table><thead><tr><th>المرجع</th><th>{{ $labels['name'] }}</th>@if($module === 'installments')<th>المدفوع</th><th>الباقي</th>@endif<th>التاريخ</th><th>{{ $labels['amount'] }}</th><th>الحالة</th><th>الإجراءات</th></tr></thead><tbody>
@forelse($records as $record)<tr><form method="POST" action="/modules/{{ $module }}/{{ $record->id }}" data-installment-form>@csrf @method('PUT')
<td><input name="reference" value="{{ $record->reference }}"></td><td><input name="name" value="{{ $record->name }}" required></td>@if($module === 'installments')<td><input type="number" step="0.01" min="0" name="paid_amount" value="{{ $record->paid_amount }}" data-paid-amount></td><td><input type="number" step="0.01" value="{{ max(0, (float) $record->amount - (float) $record->paid_amount) }}" data-remaining-amount readonly></td>@endif<td><input type="date" name="record_date" value="{{ $record->record_date->toDateString() }}" required></td><td><input type="number" step="0.01" min="0" name="amount" value="{{ $record->amount }}" @if($module === 'installments') data-total-amount @endif></td><td><select name="status">@foreach(['active'=>'فعال','pending'=>'قيد الانتظار','completed'=>'مكتمل','cancelled'=>'ملغي'] as $value=>$label)<option value="{{ $value }}" @selected($record->status===$value)>{{ $label }}</option>@endforeach</select><input type="hidden" name="notes" value="{{ $record->notes }}"></td><td><button>حفظ</button></form><form method="POST" action="/modules/{{ $module }}/{{ $record->id }}" style="display:inline">@csrf @method('DELETE')<button style="background:#B91C1C" onclick="return confirm('حذف السجل؟')">حذف</button></form></td></tr>
@empty<tr><td colspan="{{ $module === 'installments' ? 8 : 6 }}">لا توجد سجلات بعد.</td></tr>@endforelse
</tbody></table></div></div>
@if($module === 'installments')
<script>
document.querySelectorAll('[data-installment-form]').forEach(function (form) {
    const total = form.querySelector('[data-total-amount]');
    const paid = form.querySelector('[data-paid-amount]');
    const remaining = form.querySelector('[data-remaining-amount]');
    if (!total || !paid || !remaining) return;
    const updateRemaining = function () {
        remaining.value = Math.max(0, (parseFloat(total.value) || 0) - (parseFloat(paid.value) || 0)).toFixed(2);
    };
    total.addEventListener('input', updateRemaining);
    paid.addEventListener('input', updateRemaining);
});
</script>
@endif
@endsection
