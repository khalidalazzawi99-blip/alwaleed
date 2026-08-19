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
name="company_logo">

@if(!empty($setting?->company_logo))

<div style="margin-top:15px">

<img
src="{{ asset('storage/'.$setting->company_logo) }}"
style="height:90px;border-radius:14px">

</div>

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