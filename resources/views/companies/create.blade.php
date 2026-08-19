@extends('layouts.app')

@section('content')

<style>
.form-section{
    margin-bottom:30px;
}

.form-section-title{
    margin:0 0 16px;
    font-size:19px;
    font-weight:800;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:16px;
}

.form-grid-3{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:16px;
}

.field label{
    display:block;
    margin-bottom:7px;
    font-size:13px;
    font-weight:700;
    color:#6F675F;
}

.error-box{
    background:#FEF2F2;
    border:1px solid #FCA5A5;
    color:#991B1B;
    border-radius:16px;
    padding:15px 18px;
    margin-bottom:20px;
}

.create-footer{
    border-top:1px solid #E8E1D8;
    padding-top:20px;
    display:flex;
    gap:10px;
}

.secondary-btn{
    background:#F5F1EB;
    color:#6F675F;
}

.secondary-btn:hover{
    background:#EAE1D7;
}

@media(max-width:900px){
    .form-grid,
    .form-grid-3{
        grid-template-columns:1fr;
    }
}
</style>


<div class="topbar">

    <div>
        <h1 class="page-title">{{ __('إضافة شركة جديدة') }}</h1>

        <p style="color:#8A8178;margin:8px 0 0">
            {{ __('إنشاء الشركة وحساب مديرها وتحديد الاشتراك') }}
        </p>
    </div>

</div>


@if($errors->any())

<div class="error-box">

    <strong>{{ __('تأكد من المعلومات التالية:') }}</strong>

    <ul style="margin-bottom:0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>

</div>

@endif


<div class="card">

<form method="POST" action="/admin/companies">

@csrf


{{-- معلومات الشركة --}}
<div class="form-section">

    <h2 class="form-section-title">
        {{ __('معلومات الشركة') }}
    </h2>

    <div class="form-grid">

        <div class="field">
            <label>{{ __('اسم الشركة') }}</label>

            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="{{ __('مثال: شركة أضواء سيبار') }}"
                required
            >
        </div>


        <div class="field">
            <label>{{ __('كود الشركة') }}</label>

            <input
                type="text"
                name="code"
                value="{{ old('code') }}"
                placeholder="{{ __('مثال: SIPPAR') }}"
                required
            >
        </div>


        <div class="field">
            <label>{{ __('رقم الهاتف') }}</label>

            <input
                type="text"
                name="phone"
                value="{{ old('phone') }}"
                placeholder="07XXXXXXXXX"
            >
        </div>


        <div class="field">
            <label>{{ __('البريد الإلكتروني للشركة') }}</label>

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="company@example.com"
            >
        </div>

    </div>


    <div class="field" style="margin-top:16px">

        <label>{{ __('عنوان الشركة') }}</label>

        <textarea
            name="address"
            rows="3"
            placeholder="{{ __('بغداد - العراق') }}"
        >{{ old('address') }}</textarea>

    </div>

</div>


{{-- الاشتراك --}}
<div class="form-section">

    <h2 class="form-section-title">
        {{ __('الاشتراك') }}
    </h2>

    <div class="form-grid-3">

        <div class="field">

            <label>{{ __('messages.subscription_days_custom') }}</label>

            <input type="number" name="subscription_days" min="1" step="1"
                   value="{{ old('subscription_days', 30) }}"
                   list="subscription-day-presets"
                   placeholder="{{ __('messages.subscription_days_placeholder') }}" required>
            <datalist id="subscription-day-presets">
                <option value="1">{{ __('messages.one_day_test') }}</option>
                <option value="7">{{ __('messages.seven_days_test') }}</option>
                <option value="30">{{ __('messages.thirty_days') }}</option>
                <option value="90">{{ __('messages.ninety_days') }}</option>
                <option value="365">{{ __('messages.one_year') }}</option>
            </datalist>
            <small style="color:#8A8178">{{ __('messages.subscription_days_help') }}</small>

        </div>


        <div class="field">

            <label>{{ __('حالة الشركة') }}</label>

            <select name="status" required>

                <option value="active"
                    {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                    {{ __('مفعلة') }}
                </option>

                <option value="inactive"
                    {{ old('status') == 'inactive' ? 'selected' : '' }}>
                    {{ __('موقوفة') }}
                </option>

            </select>

        </div>


        <div class="field">

            <label>{{ __('الحد الأقصى للمستخدمين') }}</label>

            <select name="max_users" required>

                @foreach([5,10,20,50,100] as $count)

                    <option
                        value="{{ $count }}"
                        {{ old('max_users', '5') == $count ? 'selected' : '' }}
                    >
                        {{ __('messages.users_count', ['count' => $count]) }}
                    </option>

                @endforeach

            </select>

        </div>

    </div>

    <p style="color:#8A8178;font-size:13px;margin:12px 0 0">
        {{ __('تاريخ بداية الاشتراك يُحتسب تلقائياً من يوم إنشاء الشركة، وتاريخ الانتهاء يُحسب حسب المدة المختارة.') }}
    </p>

</div>


{{-- مدير الشركة --}}
<div class="form-section">

    <h2 class="form-section-title">
        {{ __('حساب مدير الشركة') }}
    </h2>

    <div class="form-grid-3">

        <div class="field">

            <label>{{ __('اسم المدير') }}</label>

            <input
                type="text"
                name="manager_name"
                value="{{ old('manager_name') }}"
                placeholder="{{ __('اسم مدير الشركة') }}"
                required
            >

        </div>


        <div class="field">

            <label>{{ __('البريد المستخدم لتسجيل الدخول') }}</label>

            <input
                type="email"
                name="manager_email"
                value="{{ old('manager_email') }}"
                placeholder="manager@example.com"
                required
            >

        </div>


        <div class="field">

            <label>{{ __('كلمة المرور') }}</label>

            <input
                type="password"
                name="manager_password"
                placeholder="{{ __('6 أحرف على الأقل') }}"
                required
            >

        </div>

    </div>

</div>


<div class="create-footer">

    <button type="submit">
        {{ __('إنشاء الشركة') }}
    </button>

    <a
        href="/admin/companies"
        class="btn secondary-btn"
    >
        {{ __('إلغاء') }}
    </a>

</div>


</form>

</div>

@endsection
