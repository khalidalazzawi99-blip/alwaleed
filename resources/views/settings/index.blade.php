@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">{{ __('إعدادات النظام') }}</h1>
    <p style="color:#8A8178;margin-top:8px">
        {{ __('معلومات الشركة الأساسية') }}
    </p>
</div>

<div class="card">

<form method="POST" action="/settings" enctype="multipart/form-data">
@csrf

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

<div>
<label>{{ __('اسم الشركة') }}</label>

<input
type="text"
name="company_name"
value="{{ $setting->company_name ?? '' }}">
</div>

<div><label>اسم الشركة بالعربية</label><input type="text" name="company_name_ar" dir="rtl" value="{{ old('company_name_ar', $setting->company_name_ar ?? '') }}"></div>
<div><label>اسم الشركة بالإنكليزية</label><input type="text" name="company_name_en" dir="ltr" value="{{ old('company_name_en', $setting->company_name_en ?? '') }}"></div>

<div>
<label>{{ __('رقم الهاتف') }}</label>

<input
type="text"
name="phone"
value="{{ $setting->phone ?? '' }}">
</div>

<div>
<label>{{ __('البريد الإلكتروني') }}</label>

<input
type="email"
name="email"
value="{{ $setting->email ?? '' }}">
</div>

<div>
<label>{{ __('العملة') }}</label>

<select name="currency">

<option value="IQD"
{{ ($setting->currency ?? '')=='IQD' ? 'selected':'' }}>
{{ __('دينار عراقي') }}
</option>

<option value="USD"
{{ ($setting->currency ?? '')=='USD' ? 'selected':'' }}>
{{ __('دولار أمريكي') }}
</option>

</select>

</div>

</div>

<div style="margin-top:20px">

<label>{{ __('العنوان') }}</label>

<textarea
name="address"
rows="4">{{ $setting->address ?? '' }}</textarea>

</div>

<div style="margin-top:20px">

<label>{{ __('شعار الشركة') }}</label>

<input
type="file"
name="company_logo"
accept="image/png,image/jpeg,image/webp">
<small style="display:block;margin-top:8px;color:#8A8178">{{ __('يدعم PNG وJPG وWEBP بحد أقصى 5 ميغابايت، ويبقى الشعار محفوظاً حتى استبداله.') }}</small>

@error('company_logo')
<div style="margin-top:8px;color:#B91C1C;font-weight:700">{{ $message }}</div>
@enderror

@if(!empty($setting?->company_logo))

<div style="margin-top:15px">

<img
src="{{ app(\App\Services\CompanyBrandService::class)->logoDataUri(auth()->user()?->company_id) }}"
style="height:90px;border-radius:14px">

</div>

<label style="display:flex;align-items:center;gap:8px;margin-top:12px"><input type="checkbox" name="remove_logo" value="1" style="width:auto;margin:0"> حذف الشعار الحالي صراحةً</label>

@endif

</div>

<div style="margin-top:25px">

<button type="submit">
{{ __('حفظ الإعدادات') }}
</button>

</div>

</form>

</div>

@endsection
