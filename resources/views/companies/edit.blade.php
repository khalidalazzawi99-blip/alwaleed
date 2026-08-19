@extends('layouts.app')

@section('content')

<style>
.edit-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:16px;
}

.field label{
    display:block;
    margin-bottom:7px;
    color:#6F675F;
    font-size:13px;
    font-weight:700;
}

.form-section{
    margin-bottom:28px;
}

.form-section h2{
    margin:0 0 16px;
}

.error-box{
    background:#FEF2F2;
    border:1px solid #FCA5A5;
    color:#991B1B;
    border-radius:16px;
    padding:15px 18px;
    margin-bottom:20px;
}

.actions{
    display:flex;
    gap:10px;
    border-top:1px solid #E8E1D8;
    padding-top:20px;
}

.secondary-btn{
    background:#F5F1EB;
    color:#6F675F;
}

.note-box{
    background:#F8FAFC;
    border:1px solid #E8E1D8;
    border-radius:16px;
    padding:16px;
    color:#8A8178;
    font-size:13px;
}

@media(max-width:900px){
    .edit-grid{
        grid-template-columns:1fr;
    }
}
</style>

<div class="topbar">

    <div>
        <h1 class="page-title">{{ __('تعديل الشركة') }}</h1>

        <p style="color:#8A8178;margin:8px 0 0">
            {{ __('messages.edit_company_named', ['name' => $company->name]) }}
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

<form method="POST" action="/admin/companies/{{ $company->id }}">

@csrf
@method('PUT')

<div class="form-section">

    <h2>{{ __('معلومات الشركة') }}</h2>

    <div class="edit-grid">

        <div class="field">
            <label>{{ __('اسم الشركة') }}</label>

            <input
                type="text"
                name="name"
                value="{{ old('name', $company->name) }}"
                required
            >
        </div>

        <div class="field">
            <label>{{ __('كود الشركة') }}</label>

            <input
                type="text"
                name="code"
                value="{{ old('code', $company->code) }}"
                required
            >
        </div>

        <div class="field">
            <label>{{ __('رقم الهاتف') }}</label>

            <input
                type="text"
                name="phone"
                value="{{ old('phone', $company->phone) }}"
            >
        </div>

        <div class="field">
            <label>{{ __('البريد الإلكتروني للشركة') }}</label>

            <input
                type="email"
                name="email"
                value="{{ old('email', $company->email) }}"
            >
        </div>

    </div>

    <div class="field" style="margin-top:16px">

        <label>{{ __('العنوان') }}</label>

        <textarea
            name="address"
            rows="3"
        >{{ old('address', $company->address) }}</textarea>

    </div>

</div>

<div class="form-section">

    <h2>{{ __('إعدادات الشركة') }}</h2>

    <div class="edit-grid">

        <div class="field">
            <label>{{ __('حالة الشركة') }}</label>

            <select name="status" required>

                <option
                    value="active"
                    {{ old('status', $company->status) === 'active' ? 'selected' : '' }}
                >
                    {{ __('مفعلة') }}
                </option>

                <option
                    value="inactive"
                    {{ old('status', $company->status) === 'inactive' ? 'selected' : '' }}
                >
                    {{ __('موقوفة') }}
                </option>

                <option
                    value="expired"
                    {{ old('status', $company->status) === 'expired' ? 'selected' : '' }}
                >
                    {{ __('منتهية') }}
                </option>

            </select>
        </div>

        <div class="field">
            <label>{{ __('الحد الأقصى للمستخدمين') }}</label>

            <select name="max_users" required>

                @foreach([5,10,20,50,100] as $count)

                    <option
                        value="{{ $count }}"
                        {{ old('max_users', $company->max_users) == $count ? 'selected' : '' }}
                    >
                        {{ __('messages.users_count', ['count' => $count]) }}
                    </option>

                @endforeach

            </select>
        </div>

    </div>

</div>

<div class="form-section">

    <h2>{{ __('مدير الشركة') }}</h2>

    @if($manager)

        <div class="edit-grid">

            <div class="field">
                <label>{{ __('اسم المدير') }}</label>

                <input
                    type="text"
                    name="manager_name"
                    value="{{ old('manager_name', $manager->name) }}"
                >
            </div>

            <div class="field">
                <label>{{ __('إيميل المدير') }}</label>

                <input
                    type="email"
                    name="manager_email"
                    value="{{ old('manager_email', $manager->email) }}"
                >
            </div>

            <div class="field">
                <label>{{ __('كلمة مرور جديدة') }}</label>

                <input
                    type="password"
                    name="manager_password"
                    placeholder="{{ __('اتركها فارغة إذا ما تريد تغييرها') }}"
                >
            </div>

        </div>

        <div class="note-box" style="margin-top:16px">
            {{ __('إذا ما تريد تغير كلمة المرور، خلي حقل كلمة المرور فارغ.') }}
        </div>

    @else

        <div class="edit-grid">

            <div class="field">
                <label>{{ __('اسم المدير الجديد') }}</label>

                <input
                    type="text"
                    name="manager_name"
                    value="{{ old('manager_name') }}"
                >
            </div>

            <div class="field">
                <label>{{ __('إيميل المدير الجديد') }}</label>

                <input
                    type="email"
                    name="manager_email"
                    value="{{ old('manager_email') }}"
                >
            </div>

            <div class="field">
                <label>{{ __('كلمة المرور') }}</label>

                <input
                    type="password"
                    name="manager_password"
                    placeholder="{{ __('6 أحرف على الأقل') }}"
                >
            </div>

        </div>

        <div class="note-box" style="margin-top:16px">
            {{ __('لا يوجد مدير مرتبط حالياً. تگدر تنشئ مدير جديد من هنا.') }}
        </div>

    @endif

</div>

<div class="actions">

    <button type="submit">
        {{ __('حفظ التعديلات') }}
    </button>

    <a
        href="/admin/companies/{{ $company->id }}"
        class="btn secondary-btn"
    >
        {{ __('إلغاء') }}
    </a>

</div>

</form>

</div>

@endsection
