@extends('layouts.app')

@section('content')

<style>
.owner-dashboard{
    display:flex;
    flex-direction:column;
    gap:20px;
}

.owner-hero{
    position:relative;
    overflow:hidden;
    background:linear-gradient(135deg,#FFFFFF 0%,#F7F2EB 100%);
    border:1px solid #E8E1D8;
    border-radius:30px;
    padding:32px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    box-shadow:0 20px 50px rgba(0,0,0,.05);
}

.owner-hero:after{
    content:"";
    position:absolute;
    width:280px;
    height:280px;
    border-radius:50%;
    background:rgba(205,186,158,.16);
    left:-90px;
    top:-120px;
}

.hero-content{
    position:relative;
    z-index:2;
}

.hero-content h1{
    margin:0;
    font-size:32px;
    font-weight:900;
}

.hero-content p{
    margin:8px 0 0;
    color:#8A8178;
}

.hero-actions{
    position:relative;
    z-index:2;
    display:flex;
    gap:10px;
}

.btn-soft{
    background:#F5F1EB;
    color:#6F675F;
}

.owner-stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
}

.owner-stat{
    background:#FFFFFF;
    border:1px solid #E8E1D8;
    border-radius:24px;
    padding:22px;
    box-shadow:0 12px 35px rgba(0,0,0,.035);
}

.stat-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.stat-title{
    color:#81776D;
    font-size:13px;
    font-weight:800;
}

.stat-icon{
    width:42px;
    height:42px;
    border-radius:14px;
    background:#F5F1EB;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#A68A64;
    font-weight:900;
}

.stat-number{
    font-size:34px;
    font-weight:900;
    margin:20px 0 5px;
}

.stat-note{
    margin:0;
    color:#A0978F;
    font-size:12px;
}

.green{color:#15803D}
.red{color:#B91C1C}
.orange{color:#D97706}
.gold{color:#A68A64}

.owner-grid{
    display:grid;
    grid-template-columns:1.3fr .7fr;
    gap:18px;
}

.owner-card{
    background:#FFFFFF;
    border:1px solid #E8E1D8;
    border-radius:24px;
    padding:23px;
    box-shadow:0 12px 35px rgba(0,0,0,.035);
}

.card-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:20px;
}

.card-header h2{
    margin:0;
    font-size:19px;
}

.card-link{
    color:#A68A64;
    text-decoration:none;
    font-size:13px;
    font-weight:800;
}

.expiry-list{
    display:flex;
    flex-direction:column;
    gap:11px;
}

.expiry-item{
    background:#FFFBEB;
    border:1px solid #FDE68A;
    border-radius:17px;
    padding:15px 16px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
}

.expiry-name{
    font-weight:900;
}

.expiry-date{
    color:#9A6A17;
    font-size:12px;
    margin-top:4px;
}

.expiry-days{
    padding:8px 11px;
    border-radius:12px;
    background:#FFFFFF;
    color:#92400E;
    font-size:12px;
    font-weight:900;
    white-space:nowrap;
}

.empty-message{
    color:#8A8178;
    text-align:center;
    padding:25px;
}

.quick-owner{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
}

.quick-owner a{
    text-decoration:none;
    background:#FAF9F7;
    border:1px solid #EEE8E1;
    border-radius:17px;
    padding:19px;
    color:#292522;
    font-weight:900;
    transition:.2s;
}

.quick-owner a:hover{
    background:#F5F1EB;
    transform:translateY(-2px);
}

.table-wrap{
    overflow-x:auto;
}

.owner-table{
    width:100%;
    border-collapse:collapse;
}

.owner-table th{
    color:#8A8178;
    text-align:right;
    font-size:12px;
    padding:11px;
    border-bottom:1px solid #EEE8E1;
}

.owner-table td{
    padding:15px 11px;
    border-bottom:1px solid #F2EDE7;
    background:transparent;
}

.owner-table tr:last-child td{
    border-bottom:0;
}

.company-name{
    font-weight:900;
}

.company-code{
    background:#F5F1EB;
    color:#756A60;
    padding:6px 9px;
    border-radius:10px;
    font-size:12px;
    font-weight:800;
}

.status{
    padding:7px 10px;
    border-radius:11px;
    display:inline-block;
    font-size:12px;
    font-weight:900;
}

.active{
    background:#ECFDF3;
    color:#15803D;
}

.inactive{
    background:#FFF7ED;
    color:#C2410C;
}

.expired{
    background:#FEF2F2;
    color:#B91C1C;
}

.company-view{
    color:#A68A64;
    font-weight:900;
    text-decoration:none;
}

@media(max-width:1200px){
    .owner-stats{
        grid-template-columns:repeat(2,1fr);
    }

    .owner-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:700px){
    .owner-stats,
    .quick-owner{
        grid-template-columns:1fr;
    }

    .owner-hero{
        flex-direction:column;
        align-items:flex-start;
    }

    .hero-actions{
        flex-wrap:wrap;
    }
}
</style>


<div class="owner-dashboard">

    <div class="owner-hero">

        <div class="hero-content">

            <h1>{{ __('messages.owner_welcome') }}</h1>

            <p>
                {{ __('messages.owner_intro') }}
            </p>

        </div>

        <div class="hero-actions">

            <a href="/admin/companies/create" class="btn">
                + {{ __('messages.add_company') }}
            </a>

            <a href="/admin/companies" class="btn btn-soft">
                {{ __('messages.manage_companies') }}
            </a>

        </div>

    </div>


    <div class="owner-stats">

        <div class="owner-stat">

            <div class="stat-head">
                <span class="stat-title">{{ __('messages.total_companies') }}</span>
                <div class="stat-icon">01</div>
            </div>

            <div class="stat-number">
                {{ $companiesCount }}
            </div>

            <p class="stat-note">
                {{ __('messages.registered_companies_note') }}
            </p>

        </div>


        <div class="owner-stat">

            <div class="stat-head">
                <span class="stat-title">{{ __('messages.active_companies') }}</span>
                <div class="stat-icon">02</div>
            </div>

            <div class="stat-number green">
                {{ $activeCompanies }}
            </div>

            <p class="stat-note">
                {{ __('messages.active_subscriptions_note') }}
            </p>

        </div>


        <div class="owner-stat">

            <div class="stat-head">
                <span class="stat-title">{{ __('messages.inactive_companies') }}</span>
                <div class="stat-icon">03</div>
            </div>

            <div class="stat-number orange">
                {{ $inactiveCompanies ?? 0 }}
            </div>

            <p class="stat-note">
                {{ __('messages.inactive_companies_note') }}
            </p>

        </div>


        <div class="owner-stat">

            <div class="stat-head">
                <span class="stat-title">{{ __('messages.expired_subscriptions') }}</span>
                <div class="stat-icon">04</div>
            </div>

            <div class="stat-number red">
                {{ $expiredCompanies }}
            </div>

            <p class="stat-note">
                {{ __('messages.renewal_needed') }}
            </p>

        </div>


        <div class="owner-stat">

            <div class="stat-head">
                <span class="stat-title">{{ __('messages.users') }}</span>
                <div class="stat-icon">05</div>
            </div>

            <div class="stat-number gold">
                {{ $usersCount }}
            </div>

            <p class="stat-note">
                {{ __('messages.users_in_companies') }}
            </p>

        </div>


        <div class="owner-stat">

            <div class="stat-head">
                <span class="stat-title">{{ __('messages.company_admins') }}</span>
                <div class="stat-icon">06</div>
            </div>

            <div class="stat-number">
                {{ $adminsCount ?? 0 }}
            </div>

            <p class="stat-note">
                {{ __('messages.admin_accounts_note') }}
            </p>

        </div>


        <div class="owner-stat">

            <div class="stat-head">
                <span class="stat-title">{{ __('messages.ending_soon') }}</span>
                <div class="stat-icon">07</div>
            </div>

            <div class="stat-number orange">
                {{ $endingSoonCount ?? 0 }}
            </div>

            <p class="stat-note">
                {{ __('messages.next_seven_days') }}
            </p>

        </div>


        <div class="owner-stat">

            <div class="stat-head">
                <span class="stat-title">{{ __('messages.activity_rate') }}</span>
                <div class="stat-icon">08</div>
            </div>

            <div class="stat-number green">
                {{ $companiesCount > 0
                    ? round(($activeCompanies / $companiesCount) * 100)
                    : 0 }}%
            </div>

            <p class="stat-note">
                {{ __('messages.active_companies_rate') }}
            </p>

        </div>

    </div>


    <div class="owner-grid">

        <div class="owner-card">

            <div class="card-header">

                <h2>
                    {{ __('messages.subscriptions_to_review') }}
                </h2>

                <a href="/admin/companies" class="card-link">
                    {{ __('messages.view_companies') }}
                </a>

            </div>

            <div class="expiry-list">

                @forelse($endingSoonCompanies as $company)

                    @php
                        $days = now()->startOfDay()->diffInDays(
                            \Carbon\Carbon::parse(
                                $company->subscription_end
                            )->startOfDay(),
                            false
                        );
                    @endphp

                    <div class="expiry-item">

                        <div>

                            <div class="expiry-name">
                                {{ $company->name }}
                            </div>

                            <div class="expiry-date">
                                {{ __('messages.subscription_end') }}
                                {{ \Carbon\Carbon::parse(
                                    $company->subscription_end
                                )->format('Y/m/d') }}
                            </div>

                        </div>

                        <div class="expiry-days">

                            @if($days == 0)
                                {{ __('messages.expires_today') }}
                            @else
                                {{ __('messages.days_left', ['days' => $days]) }}
                            @endif

                        </div>

                    </div>

                @empty

                    <div class="empty-message">
                        {{ __('messages.subscriptions_healthy') }}
                    </div>

                @endforelse

            </div>

        </div>


        <div class="owner-card">

            <div class="card-header">
                <h2>{{ __('messages.quick_management') }}</h2>
            </div>

            <div class="quick-owner">

                <a href="/admin/companies">
                    {{ __('messages.manage_companies') }}
                </a>

                <a href="/admin/companies/create">
                    {{ __('messages.new_company') }}
                </a>

                <a href="/users">
                    {{ __('messages.users') }}
                </a>

                <a href="/backup">
                    {{ __('messages.backup') }}
                </a>

            </div>

        </div>

    </div>


    <div class="owner-card">

        <div class="card-header">

            <h2>
                {{ __('messages.latest_companies') }}
            </h2>

            <a href="/admin/companies" class="card-link">
                {{ __('messages.view_all') }}
            </a>

        </div>

        <div class="table-wrap">

            <table class="owner-table">

                <thead>
                    <tr>
                        <th>{{ __('messages.company') }}</th>
                        <th>{{ __('messages.code') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.subscription_end') }}</th>
                        <th>{{ __('messages.users') }}</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                @forelse($latestCompanies as $company)

                    <tr>

                        <td>
                            <span class="company-name">
                                {{ $company->name }}
                            </span>
                        </td>

                        <td>
                            <span class="company-code">
                                {{ $company->code }}
                            </span>
                        </td>

                        <td>

                            @if($company->status === 'active')

                                <span class="status active">
                                    {{ __('messages.active') }}
                                </span>

                            @elseif($company->status === 'expired')

                                <span class="status expired">
                                    {{ __('messages.expired') }}
                                </span>

                            @else

                                <span class="status inactive">
                                    {{ __('messages.inactive') }}
                                </span>

                            @endif

                        </td>

                        <td>
                            {{ $company->subscription_end
                                ? \Carbon\Carbon::parse(
                                    $company->subscription_end
                                )->format('Y/m/d')
                                : '-' }}
                        </td>

                        <td>
                            {{ $company->users_count ?? 0 }}
                            /
                            {{ $company->max_users }}
                        </td>

                        <td>
                            <a
                                href="/admin/companies/{{ $company->id }}"
                                class="company-view"
                            >
                                {{ __('messages.manage') }}
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="empty-message">
                            {{ __('messages.no_companies') }}
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
