@extends('layouts.app')
@section('content')
@include('kudia._styles')
<div class="kudia-page">
    @include('kudia._nav')
    @if($errors->any())<div class="kudia-errors">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <details class="kudia-panel" @if($errors->any()) open @endif><summary style="font-weight:800;cursor:pointer">+ إضافة زبون جديد</summary>
        <form method="POST" action="{{ route('kudia.customers.store') }}" style="margin-top:20px">@csrf
            <div class="kudia-form-grid">
                <div class="kudia-field"><label>اسم الزبون</label><input name="name" value="{{ old('name') }}" maxlength="255" required></div>
                <div class="kudia-field"><label>الهاتف</label><input name="phone" value="{{ old('phone') }}" maxlength="50"></div>
                <div class="kudia-field"><label>الشركة</label><input name="company_name" value="{{ old('company_name') }}" maxlength="255"></div>
                <div class="kudia-field"><label>العنوان</label><input name="address" value="{{ old('address') }}"></div>
                <div class="kudia-field wide"><label>ملاحظات</label><textarea name="notes">{{ old('notes') }}</textarea></div>
            </div><button type="submit" style="margin-top:18px">حفظ الزبون</button>
        </form>
    </details>
    <section class="kudia-panel"><div class="kudia-toolbar"><h2>الزبائن وكشوفات الحساب</h2><div class="kudia-nav"><a href="{{ route('kudia.customers.index') }}">الكل</a><a href="{{ route('kudia.customers.index', ['debts_only' => 1]) }}">أصحاب الأرصدة المستحقة</a></div></div>
        <div style="overflow-x:auto"><table class="kudia-table"><thead><tr><th>الزبون</th><th>الهاتف</th><th>المسدد</th><th>الرصيد المتبقي</th><th>كشف الحساب</th></tr></thead><tbody>
        @forelse($customers as $customer)
            <tr><td><a href="{{ route('kudia.customers.show', $customer) }}">{{ $customer->name }}</a></td><td><bdi>{{ $customer->phone ?: '—' }}</bdi></td><td><bdi>{{ number_format($customer->paid_amount, 2) }}</bdi></td><td><bdi>{{ number_format($customer->remaining_amount, 2) }}</bdi> {{ $companyCurrency }}</td><td><a href="{{ route('kudia.customers.pdf', $customer) }}">PDF</a> · <a href="{{ route('kudia.customers.excel', $customer) }}">Excel</a> · <a href="/customers/{{ $customer->id }}/edit">تعديل</a></td></tr>
        @empty
            <tr><td colspan="5" class="kudia-empty">لا يوجد زبائن.</td></tr>
        @endforelse
        </tbody></table></div>
    </section>
</div>
@endsection
