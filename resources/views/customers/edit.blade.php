@extends('layouts.app')

@section('content')
<div class="topbar"><h1 class="page-title">{{ __('تعديل بيانات الزبون') }}</h1></div>
<div class="card">
    <form method="POST" action="/customers/{{ $customer->id }}">
        @csrf
        @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
            <input type="text" name="name" value="{{ old('name', $customer->name) }}" placeholder="{{ __('اسم الزبون') }}" required>
            <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" placeholder="{{ __('رقم الهاتف') }}">
            <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" placeholder="{{ __('اسم الشركة') }}">
            <input type="text" name="address" value="{{ old('address', $customer->address) }}" placeholder="{{ __('العنوان') }}">
        </div>
        <br>
        <textarea name="notes" rows="4" placeholder="{{ __('ملاحظات') }}">{{ old('notes', $customer->notes) }}</textarea>
        <br><br>
        <button type="submit">{{ __('حفظ التعديلات') }}</button>
        <a href="/customers" class="btn">{{ __('إلغاء') }}</a>
    </form>
</div>
@endsection
