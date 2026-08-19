@extends('layouts.app')

@section('content')

@php
    $daysLeft = $daysLeft ?? null;
@endphp

<style>
.company-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:18px;
}

.info-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:22px;
}

.info-card{
    background:#fff;
    border:1px solid #E8E1D8;
    border-radius:20px;
    padding:20px;
}

.info-card p{
    color:#8A8178;
    margin:0 0 8px;
}

.info-card strong{
    font-size:20px;
}

.action-grid{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    align-items:center;
}

.status-active{
    color:#15803D;
}

.status-inactive{
    color:#D97706;
}

.status-expired{
    color:#B91C1C;
}

.danger-zone{
    margin-top:22px;
    padding-top:20px;
    border-top:1px solid #F3D1D1;
}

.danger-zone h3{
    color:#B91C1C;
    margin:0 0 8px;
}

.danger-zone p{
    color:#8A8178;
    margin:0 0 15px;
}

.delete-btn{
    background:#DC2626 !important;
}

.delete-btn:hover{
    background:#B91C1C !important;
}

.feature-panel{overflow:hidden;border:1px solid #C9B59C;background:linear-gradient(145deg,#FFFDF9 0%,#F6EBDD 100%)}
.feature-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:20px}
.feature-heading h2{margin:0 0 7px}.feature-heading p{margin:0;color:#8A8178}
.feature-count{padding:8px 13px;border-radius:999px;background:#F1E8DB;color:#765B36;font-weight:700;white-space:nowrap}
.feature-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.feature-item{display:flex;align-items:center;gap:14px;padding:16px;border:1px solid #C9B59C;border-radius:17px;background:linear-gradient(135deg,#F5EBDD,#E7D4BB);box-shadow:0 7px 18px rgba(159,130,99,.13);transition:.2s ease}
.feature-item.is-enabled{border-color:#9F8263;background:linear-gradient(135deg,#D8C5AC,#BDA184);box-shadow:0 10px 26px rgba(159,130,99,.25)}
.feature-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:14px;background:rgba(255,255,255,.62);border:1px solid rgba(159,130,99,.22);font-size:22px;flex:0 0 auto}
.feature-copy{min-width:0;flex:1}.feature-copy strong{display:block;margin-bottom:4px}.feature-copy small{display:block;color:#8A8178;line-height:1.55}
.feature-state{font-size:12px;font-weight:800;color:#8B6D4E;margin-top:5px}.is-enabled .feature-state{color:#59432E}
.feature-switch{position:relative;width:50px;height:28px;flex:0 0 auto}.feature-switch input{position:absolute;opacity:0;pointer-events:none}
.feature-slider{position:absolute;inset:0;border-radius:999px;background:#D8D0C7;cursor:pointer;transition:.2s}
.feature-slider:after{content:'';position:absolute;width:22px;height:22px;top:3px;left:3px;border-radius:50%;background:#fff;box-shadow:0 2px 7px #0003;transition:.2s}
.feature-switch input:checked + .feature-slider{background:linear-gradient(135deg,#BDA78B,#9F8263)}.feature-switch input:checked + .feature-slider:after{transform:translateX(22px)}
[dir=rtl] .feature-slider:after{left:auto;right:3px}[dir=rtl] .feature-switch input:checked + .feature-slider:after{transform:translateX(-22px)}
.feature-switch input:disabled + .feature-slider{opacity:.55;cursor:wait}

@media(max-width:1000px){
    .info-grid{
        grid-template-columns:1fr 1fr;
    }
}

@media(max-width:650px){
    .info-grid{
        grid-template-columns:1fr;
    }

    .company-head{
        flex-direction:column;
        align-items:flex-start;
    }

    .feature-grid{grid-template-columns:1fr}.feature-heading{flex-direction:column}
}
</style>


<div class="topbar company-head">

    <div>
        <h1 class="page-title">
            {{ $company->name }}
        </h1>

        <p style="color:#8A8178;margin:8px 0 0">
            {{ __('messages.company_code_value', ['code' => $company->code]) }}
        </p>
    </div>

    <a href="/admin/companies" class="btn">
        {{ __('رجوع للشركات') }}
    </a>

</div>


@if(session('success'))

<div
    class="card"
    style="
        background:#ECFDF3;
        border-color:#86EFAC;
        color:#166534
    "
>
    {{ session('success') }}
</div>

@endif


<div class="info-grid">

    <div class="info-card">

        <p>{{ __('حالة الشركة') }}</p>

        @if($company->status === 'active')

            <strong class="status-active">
                {{ __('مفعلة') }}
            </strong>

        @elseif($company->status === 'expired')

            <strong class="status-expired">
                {{ __('منتهية') }}
            </strong>

        @else

            <strong class="status-inactive">
                {{ __('موقوفة') }}
            </strong>

        @endif

    </div>


    <div class="info-card">

        <p>{{ __('بداية الاشتراك') }}</p>

        <strong>
            {{ $company->subscription_start
                ? \Carbon\Carbon::parse($company->subscription_start)->format('Y/m/d')
                : '-' }}
        </strong>

    </div>


    <div class="info-card">

        <p>{{ __('نهاية الاشتراك') }}</p>

        <strong>
            {{ $company->subscription_end
                ? \Carbon\Carbon::parse($company->subscription_end)->format('Y/m/d')
                : '-' }}
        </strong>

    </div>


    <div class="info-card">

        <p>{{ __('الأيام المتبقية') }}</p>

        @if($daysLeft === null)

            <strong>-</strong>

        @elseif($daysLeft < 0)

            <strong style="color:#B91C1C">
                {{ __('منتهي') }}
            </strong>

        @elseif($daysLeft == 0)

            <strong style="color:#D97706">
                {{ __('ينتهي اليوم') }}
            </strong>

        @else

            <strong>
                            {{ __('messages.days_remaining', ['days' => $daysLeft]) }}
            </strong>

        @endif

    </div>

</div>


<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">

    <div class="card">

        <h2>{{ __('معلومات الشركة') }}</h2>

        <div style="line-height:2.4">

            <div>
                <strong>{{ __('الهاتف:') }}</strong>
                {{ $company->phone ?: '-' }}
            </div>

            <div>
                <strong>{{ __('البريد:') }}</strong>
                {{ $company->email ?: '-' }}
            </div>

            <div>
                <strong>{{ __('العنوان:') }}</strong>
                {{ $company->address ?: '-' }}
            </div>

            <div>
                <strong>{{ __('عدد المستخدمين:') }}</strong>
                {{ $company->users->count() }} / {{ $company->max_users }}
            </div>

        </div>

    </div>


    <div class="card">

        <h2>{{ __('مدير الشركة') }}</h2>

        @if($manager)

            <div style="line-height:2.4">

                <div>
                    <strong>{{ __('الاسم:') }}</strong>
                    {{ $manager->name }}
                </div>

                <div>
                    <strong>{{ __('البريد:') }}</strong>
                    {{ $manager->email }}
                </div>

            </div>

        @else

            <p style="color:#8A8178">
                {{ __('لا يوجد مدير مرتبط بهذه الشركة.') }}
            </p>

        @endif

    </div>

</div>


<div class="card feature-panel" id="feature-modules">
    <div class="feature-heading">
        <div>
            <h2>{{ __('messages.feature_modules') }}</h2>
            <p>{{ __('messages.feature_modules_description') }}</p>
        </div>
        <span class="feature-count">
            <span id="enabled-feature-count">{{ $featureModules->where('enabled', true)->count() }}</span> / {{ $featureModules->count() }}
            {{ __('messages.enabled_features') }}
        </span>
    </div>

    <div class="feature-grid">
        @foreach($featureModules as $module)
            <div class="feature-item {{ $module['enabled'] ? 'is-enabled' : '' }}" data-feature-item>
                <span class="feature-icon" aria-hidden="true">{{ $module['icon'] }}</span>
                <div class="feature-copy">
                    <strong>{{ __($module['name']) }}</strong>
                    <small>{{ __($module['description']) }}</small>
                    <div class="feature-state" data-state
                         data-on="{{ __('messages.enabled') }}"
                         data-off="{{ __('messages.disabled') }}">
                        {{ $module['enabled'] ? __('messages.enabled') : __('messages.disabled') }}
                    </div>
                </div>
                <label class="feature-switch" title="{{ __($module['name']) }}">
                    <input type="checkbox" data-feature-toggle value="{{ $module['key'] }}" @checked($module['enabled'])>
                    <span class="feature-slider"></span>
                </label>
            </div>
        @endforeach
    </div>
</div>


<div class="card">

    <h2>{{ __('إدارة الاشتراك') }}</h2>

    <p style="color:#8A8178">
        {{ __('التجديد متاح لك فقط كمالك النظام.') }}
    </p>

    <form
        method="POST"
        action="/admin/companies/{{ $company->id }}/renew"
        style="
            display:flex;
            gap:10px;
            align-items:center;
            flex-wrap:wrap;
            margin-top:15px
        "
    >

        @csrf

        <div style="min-width:230px">
            <label for="renewal-days" style="display:block;margin-bottom:7px;font-weight:700">
                {{ __('messages.number_of_days') }}
            </label>
            <input id="renewal-days" type="number" name="days" min="1" step="1" value="30"
                   list="renewal-day-presets"
                   placeholder="{{ __('messages.subscription_days_placeholder') }}" required>
            <datalist id="renewal-day-presets">
                <option value="1">{{ __('messages.one_day_test') }}</option>
                <option value="7">{{ __('messages.seven_days_test') }}</option>
                <option value="30">{{ __('messages.thirty_days') }}</option>
                <option value="90">{{ __('messages.ninety_days') }}</option>
                <option value="365">{{ __('messages.one_year') }}</option>
            </datalist>
        </div>

        <button type="submit">
            {{ __('تجديد الاشتراك') }}
        </button>

    </form>

</div>


<div class="card">

    <h2>{{ __('إدارة الشركة') }}</h2>

    <div class="action-grid">

        <a
            href="/admin/companies/{{ $company->id }}/edit"
            class="btn"
        >
            {{ __('تعديل بيانات الشركة والمدير') }}
        </a>


        @if($company->status !== 'active')

            <form
                method="POST"
                action="/admin/companies/{{ $company->id }}/activate"
            >

                @csrf

                <button
                    type="submit"
                    style="background:#15803D"
                >
                    {{ __('تفعيل الشركة') }}
                </button>

            </form>

        @else

            <form
                method="POST"
                action="/admin/companies/{{ $company->id }}/deactivate"
            >

                @csrf

                <button
                    type="submit"
                    style="background:#B91C1C"
                >
                    {{ __('إيقاف الشركة') }}
                </button>

            </form>

        @endif

    </div>


    <div class="danger-zone">

        <h3>
            {{ __('حذف الشركة') }}
        </h3>

        <p>
            {{ __('حذف الشركة سيؤدي إلى حذف جميع حسابات المستخدمين المرتبطين بها.') }}
            {{ __('هذا الإجراء لا يمكن التراجع عنه.') }}
        </p>

        <form
            method="POST"
            action="/admin/companies/{{ $company->id }}"
            onsubmit="return confirm(@js(__('messages.confirm_delete_company', ['name' => $company->name])))"
        >

            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="delete-btn"
            >
                {{ __('حذف الشركة نهائياً') }}
            </button>

        </form>

    </div>

</div>


<div class="card">

    <h2>{{ __('موظفو الشركة') }}</h2>

    <table>

        <thead>

            <tr>
                <th>{{ __('الاسم') }}</th>
                <th>{{ __('البريد') }}</th>
                <th>{{ __('الصلاحية') }}</th>
            </tr>

        </thead>

        <tbody>

        @forelse($company->users as $user)

            <tr>

                <td>
                    {{ $user->name }}
                </td>

                <td>
                    {{ $user->email }}
                </td>

                <td>

                    @if($user->role === 'admin')

                        {{ __('مدير') }}

                    @elseif($user->role === 'accountant')

                        {{ __('محاسب') }}

                    @elseif($user->role === 'data_entry')

                        {{ __('إدخال بيانات') }}

                    @else

                        {{ __('مشاهدة فقط') }}

                    @endif

                </td>

            </tr>

        @empty

            <tr>

                <td colspan="3">
                    {{ __('لا يوجد مستخدمون لهذه الشركة') }}
                </td>

            </tr>

        @endforelse

        </tbody>

    </table>

</div>

<script>
document.querySelectorAll('[data-feature-toggle]').forEach((toggle) => {
    toggle.addEventListener('change', async () => {
        const item = toggle.closest('[data-feature-item]');
        const state = item.querySelector('[data-state]');
        const requested = toggle.checked;
        toggle.disabled = true;

        try {
            const response = await fetch(@js(route('admin.companies.features.update', $company)), {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @js(csrf_token()),
                },
                body: JSON.stringify({feature_key: toggle.value, enabled: requested}),
            });
            if (!response.ok) throw new Error('feature-update-failed');

            const data = await response.json();
            toggle.checked = Boolean(data.enabled);
            item.classList.toggle('is-enabled', toggle.checked);
            state.textContent = toggle.checked ? state.dataset.on : state.dataset.off;
            document.getElementById('enabled-feature-count').textContent =
                document.querySelectorAll('[data-feature-toggle]:checked').length;
        } catch (error) {
            toggle.checked = !requested;
            alert(@js(__('messages.feature_update_failed')));
        } finally {
            toggle.disabled = false;
        }
    });
});
</script>

@endsection
