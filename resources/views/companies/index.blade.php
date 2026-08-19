@extends('layouts.app')

@section('content')

<style>
.companies-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
}

.companies-header p{
    color:#8A8178;
    margin:8px 0 0;
}

.company-name{
    font-weight:800;
    font-size:15px;
}

.company-code{
    display:inline-block;
    background:#F5F1EB;
    color:#8A8178;
    padding:6px 10px;
    border-radius:10px;
    font-size:13px;
    font-weight:700;
}

.status{
    display:inline-block;
    padding:7px 12px;
    border-radius:12px;
    font-size:13px;
    font-weight:800;
}

.status-active{
    background:#ECFDF3;
    color:#15803D;
}

.status-expired{
    background:#FEF2F2;
    color:#B91C1C;
}

.status-inactive{
    background:#F5F1EB;
    color:#8A8178;
}

.subscription-date{
    font-weight:700;
}

.days-left{
    display:block;
    font-size:12px;
    margin-top:4px;
}

.days-good{
    color:#15803D;
}

.days-warning{
    color:#D97706;
}

.days-expired{
    color:#B91C1C;
}

.users-count{
    font-weight:800;
}

.action-btn{
    display:inline-block;
    padding:9px 14px;
    border-radius:11px;
    text-decoration:none;
    font-size:13px;
    font-weight:800;
    background:#CDBA9E;
    color:#fff;
}

.action-btn:hover{
    background:#BFA98A;
}

.success-message{
    background:#ECFDF3;
    border:1px solid #86EFAC;
    color:#166534;
    padding:15px 18px;
    border-radius:16px;
    margin-bottom:20px;
    font-weight:700;
}

@media(max-width:900px){
    .companies-table{
        overflow-x:auto;
    }

    .companies-header{
        align-items:flex-start;
        flex-direction:column;
    }
}
</style>


<div class="topbar companies-header">

    <div>
        <h1 class="page-title">{{ __('الشركات المشتركة') }}</h1>

        <p>
            {{ __('إدارة الشركات والاشتراكات والمستخدمين') }}
        </p>
    </div>

    <a href="/admin/companies/create" class="btn">
        {{ __('+ إضافة شركة') }}
    </a>

</div>


@if(session('success'))
    <div class="success-message">
        {{ session('success') }}
    </div>
@endif


<div class="card">

    <div class="companies-table">

        <table>

            <thead>
                <tr>
                    <th>{{ __('الشركة') }}</th>
                    <th>{{ __('الكود') }}</th>
                    <th>{{ __('الحالة') }}</th>
                    <th>{{ __('نهاية الاشتراك') }}</th>
                    <th>{{ __('المستخدمين') }}</th>
                    <th>{{ __('الإجراءات') }}</th>
                </tr>
            </thead>

            <tbody>

            @forelse($companies as $company)

                @php
                    $daysLeft = null;

                    if ($company->subscription_end) {
                        $daysLeft = now()
                            ->startOfDay()
                            ->diffInDays(
                                \Carbon\Carbon::parse($company->subscription_end)->startOfDay(),
                                false
                            );
                    }
                @endphp

                <tr>

                    {{-- اسم الشركة --}}
                    <td>
                        <div class="company-name">
                            {{ $company->name }}
                        </div>

                        @if($company->email)
                            <div style="font-size:12px;color:#8A8178;margin-top:4px">
                                {{ $company->email }}
                            </div>
                        @endif
                    </td>


                    {{-- كود الشركة --}}
                    <td>
                        <span class="company-code">
                            {{ $company->code }}
                        </span>
                    </td>


                    {{-- الحالة --}}
                    <td>

                        @if($company->status === 'active')

                            <span class="status status-active">
                                {{ __('فعال') }}
                            </span>

                        @elseif($company->status === 'expired')

                            <span class="status status-expired">
                                {{ __('منتهي') }}
                            </span>

                        @else

                            <span class="status status-inactive">
                                {{ __('موقوف') }}
                            </span>

                        @endif

                    </td>


                    {{-- الاشتراك --}}
                    <td>

                        @if($company->subscription_end)

                            <div class="subscription-date">
                                {{ \Carbon\Carbon::parse($company->subscription_end)->format('Y/m/d') }}
                            </div>

                            @if($daysLeft < 0)

                                <span class="days-left days-expired">
                                    {{ __('انتهى الاشتراك') }}
                                </span>

                            @elseif($daysLeft == 0)

                                <span class="days-left days-warning">
                                    {{ __('ينتهي اليوم') }}
                                </span>

                            @elseif($daysLeft <= 7)

                                <span class="days-left days-warning">
                                {{ __('messages.days_left', ['days' => $daysLeft]) }}
                                </span>

                            @else

                                <span class="days-left days-good">
                                {{ __('messages.days_left', ['days' => $daysLeft]) }}
                                </span>

                            @endif

                        @else

                            <span style="color:#8A8178">
                                {{ __('غير محدد') }}
                            </span>

                        @endif

                    </td>


                    {{-- المستخدمين --}}
                    <td>
                        <span class="users-count">
                            {{ $company->users_count ?? 0 }}
                            /
                            {{ $company->max_users }}
                        </span>
                    </td>


                    {{-- الإجراءات --}}
                    <td>

                        <a
                            href="/admin/companies/{{ $company->id }}"
                            class="action-btn"
                        >
                            {{ __('عرض التفاصيل') }}
                        </a>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" style="text-align:center;padding:35px;color:#8A8178">
                        {{ __('لا توجد شركات مضافة حالياً') }}
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
