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
    <form method="POST" action="/modules/{{ $module }}" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">@csrf
        <div><label>الرقم المرجعي</label><input name="reference" value="{{ old('reference') }}"></div>
        <div><label>{{ $labels['name'] }}</label><input name="name" value="{{ old('name') }}" required></div>
        <div><label>التاريخ</label><input type="date" name="record_date" value="{{ old('record_date', now()->toDateString()) }}" required></div>
        <div><label>{{ $labels['amount'] }}</label><input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', 0) }}"></div>
        <div><label>الحالة</label><select name="status"><option value="active">فعال</option><option value="pending">قيد الانتظار</option><option value="completed">مكتمل</option><option value="cancelled">ملغي</option></select></div>
        <div><label>ملاحظات</label><input name="notes" value="{{ old('notes') }}"></div>
        <div><button type="submit">إضافة السجل</button></div>
    </form>
</div>
<div class="card"><h2>السجلات</h2><div style="overflow:auto"><table><thead><tr><th>المرجع</th><th>{{ $labels['name'] }}</th><th>التاريخ</th><th>{{ $labels['amount'] }}</th><th>الحالة</th><th>الإجراءات</th></tr></thead><tbody>
@forelse($records as $record)<tr><form method="POST" action="/modules/{{ $module }}/{{ $record->id }}">@csrf @method('PUT')
<td><input name="reference" value="{{ $record->reference }}"></td><td><input name="name" value="{{ $record->name }}" required></td><td><input type="date" name="record_date" value="{{ $record->record_date->toDateString() }}" required></td><td><input type="number" step="0.01" min="0" name="amount" value="{{ $record->amount }}"></td><td><select name="status">@foreach(['active'=>'فعال','pending'=>'قيد الانتظار','completed'=>'مكتمل','cancelled'=>'ملغي'] as $value=>$label)<option value="{{ $value }}" @selected($record->status===$value)>{{ $label }}</option>@endforeach</select><input type="hidden" name="notes" value="{{ $record->notes }}"></td><td><button>حفظ</button></form><form method="POST" action="/modules/{{ $module }}/{{ $record->id }}" style="display:inline">@csrf @method('DELETE')<button style="background:#B91C1C" onclick="return confirm('حذف السجل؟')">حذف</button></form></td></tr>
@empty<tr><td colspan="6">لا توجد سجلات بعد.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
