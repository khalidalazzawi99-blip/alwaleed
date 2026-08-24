@extends('layouts.app')

@section('content')

<style>
.dashboard-wrap{
    display:flex;
    flex-direction:column;
    gap:20px;
}

.dashboard-head{
    background:linear-gradient(135deg,#FFFFFF 0%,#F8F4EE 100%);
    border:1px solid #E8E1D8;
    border-radius:24px;
    padding:24px 26px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    box-shadow:0 12px 35px rgba(31,31,31,.04);
}

.dashboard-head h1{
    margin:0;
    font-size:30px;
    font-weight:900;
}

.dashboard-head p{
    margin:7px 0 0;
    color:#8A8178;
}

.head-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.head-btn{
    text-decoration:none;
    border-radius:13px;
    padding:11px 18px;
    font-weight:800;
    font-size:14px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

.receipt-btn{
    background:#15803D;
    color:white;
}

.payment-btn{
    background:#B91C1C;
    color:white;
}

.alert{
    border-radius:18px;
    padding:17px 20px;
}

.alert h3{
    margin:0 0 5px;
    font-size:16px;
}

.alert p{
    margin:0;
    font-size:14px;
}

.alert-warning{
    background:#FFFBEB;
    border:1px solid #FCD34D;
    color:#92400E;
}

.alert-danger{
    background:#FEF2F2;
    border:1px solid #FCA5A5;
    color:#991B1B;
}

.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
}

.stat-box{
    background:#FFFFFF;
    border:1px solid #E8E1D8;
    border-radius:21px;
    padding:21px;
    box-shadow:0 10px 30px rgba(0,0,0,.035);
    min-width:0;
}

.stat-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}

.stat-title{
    margin:0;
    color:#7D746B;
    font-size:14px;
    font-weight:700;
}

.stat-icon{
    width:38px;
    height:38px;
    background:#F5F1EB;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:17px;
}

.stat-value{
    margin:18px 0 5px;
    font-size:28px;
    font-weight:900;
    line-height:1.1;
    overflow-wrap:anywhere;
}

.stat-small{
    margin:0;
    color:#9B9188;
    font-size:12px;
}

.green{color:#15803D}
.red{color:#B91C1C}
.gold{color:#AD9270}

.section-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.dashboard-card{
    background:#FFFFFF;
    border:1px solid #E8E1D8;
    border-radius:22px;
    padding:22px;
    box-shadow:0 10px 30px rgba(0,0,0,.035);
}

.card-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:18px;
}

.card-head h2{
    margin:0;
    font-size:18px;
}

.card-head a{
    color:#A68A64;
    text-decoration:none;
    font-size:13px;
    font-weight:800;
}

.movement-box{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
}

.movement-item{
    background:#FAF9F7;
    border:1px solid #EEE8E1;
    border-radius:16px;
    padding:17px;
}

.movement-item span{
    display:block;
    color:#8A8178;
    font-size:12px;
    margin-bottom:9px;
}

.movement-item strong{
    font-size:18px;
}

.quick-actions{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:12px;
}

.quick-action{
    background:#FAF9F7;
    border:1px solid #EEE8E1;
    border-radius:16px;
    padding:17px;
    text-decoration:none;
    color:#282522;
    font-weight:800;
    transition:.2s;
}

.quick-action:hover{
    background:#F5F1EB;
    transform:translateY(-2px);
}

.mini-stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
}

.mini-stat{
    background:#FFFFFF;
    border:1px solid #E8E1D8;
    border-radius:20px;
    padding:20px;
}

.mini-stat p{
    margin:0 0 12px;
    color:#8A8178;
    font-size:13px;
}

.mini-stat strong{
    font-size:25px;
}

.company-box{
    background:#F8F5F0;
    border:1px solid #E8E1D8;
    border-radius:18px;
    padding:18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
}

.company-box h3{
    margin:0 0 5px;
}

.company-box p{
    margin:0;
    color:#8A8178;
    font-size:13px;
}

.subscription{
    text-align:left;
}

.subscription span{
    display:block;
    color:#8A8178;
    font-size:12px;
}

.subscription strong{
    display:block;
    margin-top:5px;
}

.simple-table{
    width:100%;
    border-collapse:collapse;
}

.simple-table th{
    padding:10px 12px;
    color:#8A8178;
    font-size:12px;
    font-weight:700;
    border-bottom:1px solid #EEE8E1;
    text-align:right;
}

.simple-table td{
    background:transparent;
    padding:14px 12px;
    border-bottom:1px solid #F2EDE7;
}

.simple-table tr:last-child td{
    border-bottom:0;
}

.simple-table td:first-child,
.simple-table td:last-child{
    border-radius:0;
}

.empty-state{
    text-align:center;
    color:#A29A92;
    padding:25px !important;
}

@media(max-width:1200px){
    .stats{
        grid-template-columns:repeat(2,1fr);
    }
}

@media(max-width:900px){
    .section-grid,
    .movement-box,
    .mini-stats{
        grid-template-columns:1fr;
    }

    .dashboard-head{
        flex-direction:column;
        align-items:flex-start;
    }
}

@media(max-width:600px){
    .stats,
    .quick-actions{
        grid-template-columns:1fr;
    }
}
</style>


<div class="dashboard-wrap">

    {{-- Header --}}
    <div class="dashboard-head">

        <div>
            <h1>
                {{ $company?->name ?? __('messages.dashboard') }}
            </h1>

            <p>
                {{ __('messages.welcome_summary', ['name' => auth()->user()->name]) }}
            </p>
        </div>

        <div class="head-actions">

            <a href="/receipts" class="head-btn receipt-btn">
                + {{ __('messages.add_receipt') }}
            </a>

            <a href="/payments" class="head-btn payment-btn">
                + {{ __('messages.add_payment') }}
            </a>

        </div>

    </div>


    {{-- تنبيه الاشتراك --}}
    @if(
        $company &&
        $subscriptionDaysLeft !== null &&
        $subscriptionDaysLeft >= 0 &&
        $subscriptionDaysLeft <= 7
    )

        <div class="alert alert-warning">

            <h3>
                {{ __('messages.subscription_ending_title') }}
            </h3>

            <p>
                {{ __('messages.subscription_ending_text', ['days' => $subscriptionDaysLeft]) }}
            </p>

        </div>

    @endif


    {{-- تنبيه الصندوق --}}
    @if($balance < $lowBalanceThreshold)

        <div class="alert alert-danger">

            <h3>
                {{ __('messages.low_balance_title') }}
            </h3>

            <p>
                {{ __('messages.low_balance_text', ['threshold' => number_format($lowBalanceThreshold, 0), 'currency' => $companyCurrency]) }}
            </p>

        </div>

    @endif


    {{-- الأرقام الأساسية --}}
    <div class="stats">

        <div class="stat-box">

            <div class="stat-top">
                <p class="stat-title">{{ __('messages.cashbox_balance') }}</p>
                <div class="stat-icon">◉</div>
            </div>

            <div class="stat-value gold">
                {{ number_format($balance, 0) }}
            </div>

            <p class="stat-small">
                {{ $companyCurrency }}
            </p>

        </div>


        <div class="stat-box">

            <div class="stat-top">
                <p class="stat-title">{{ __('messages.total_receipts') }}</p>
                <div class="stat-icon">↓</div>
            </div>

            <div class="stat-value green">
                {{ number_format($totalReceipts, 0) }}
            </div>

            <p class="stat-small">
                {{ __('messages.receipt_count', ['count' => $receiptsCount]) }}
            </p>

        </div>


        <div class="stat-box">

            <div class="stat-top">
                <p class="stat-title">{{ __('messages.total_payments') }}</p>
                <div class="stat-icon">↑</div>
            </div>

            <div class="stat-value red">
                {{ number_format($totalPayments, 0) }}
            </div>

            <p class="stat-small">
                {{ __('messages.payment_count', ['count' => $paymentsCount]) }}
            </p>

        </div>


        <div class="stat-box">

            <div class="stat-top">
                <p class="stat-title">{{ __('messages.net_movement') }}</p>
                <div class="stat-icon">≈</div>
            </div>

            <div class="stat-value {{ $netMovement >= 0 ? 'green' : 'red' }}">
                {{ number_format($netMovement, 0) }}
            </div>

            <p class="stat-small">
                {{ __('messages.receipts_minus_payments') }}
            </p>

        </div>

    </div>


    {{-- حركة اليوم --}}
    <div class="dashboard-card">

        <div class="card-head">

            <h2>
                {{ __('messages.today_activity') }}
            </h2>

            <span style="color:#9B9188;font-size:12px">
                {{ now()->format('Y/m/d') }}
            </span>

        </div>

        <div class="movement-box">

            <div class="movement-item">

                <span>
                    {{ __('messages.received_today') }}
                </span>

                <strong class="green">
                    {{ number_format($todayReceipts, 0) }} {{ $companyCurrency }}
                </strong>

            </div>


            <div class="movement-item">

                <span>
                    {{ __('messages.paid_today') }}
                </span>

                <strong class="red">
                    {{ number_format($todayPayments, 0) }} {{ $companyCurrency }}
                </strong>

            </div>


            <div class="movement-item">

                <span>
                    {{ __('messages.today_net') }}
                </span>

                <strong class="{{ $todayNet >= 0 ? 'green' : 'red' }}">
                    {{ number_format($todayNet, 0) }} {{ $companyCurrency }}
                </strong>

            </div>

        </div>

    </div>


    {{-- حركة الشهر + الإجراءات --}}
    <div class="section-grid">

        <div class="dashboard-card">

            <div class="card-head">

                <h2>
                    {{ __('messages.current_month') }}
                </h2>

            </div>

            <div class="movement-box">

                <div class="movement-item">

                    <span>
                        {{ __('messages.total_receipts') }}
                    </span>

                    <strong class="green">
                        {{ number_format($monthReceipts, 0) }}
                    </strong>

                </div>


                <div class="movement-item">

                    <span>
                        {{ __('messages.total_payments') }}
                    </span>

                    <strong class="red">
                        {{ number_format($monthPayments, 0) }}
                    </strong>

                </div>


                <div class="movement-item">

                    <span>
                        {{ __('messages.month_net') }}
                    </span>

                    <strong class="{{ $monthNet >= 0 ? 'green' : 'red' }}">
                        {{ number_format($monthNet, 0) }}
                    </strong>

                </div>

            </div>

        </div>


        <div class="dashboard-card">

            <div class="card-head">

                <h2>
                    {{ __('messages.quick_access') }}
                </h2>

            </div>

            <div class="quick-actions">

                <a href="/customers" class="quick-action">
                    {{ __('messages.customers') }}
                </a>

                <a href="/suppliers" class="quick-action">
                    {{ __('messages.suppliers') }}
                </a>

                <a href="/cashbox" class="quick-action">
                    {{ __('messages.cashbox') }}
                </a>

                <a href="/reports" class="quick-action">
                    {{ __('messages.reports') }}
                </a>

            </div>

        </div>

    </div>


    {{-- إحصائيات الأشخاص --}}
    <div class="mini-stats">

        <div class="mini-stat">

            <p>
                {{ __('messages.customers') }}
            </p>

            <strong>
                {{ $customers }}
            </strong>

        </div>


        <div class="mini-stat">

            <p>
                {{ __('messages.suppliers') }}
            </p>

            <strong>
                {{ $suppliers }}
            </strong>

        </div>


        <div class="mini-stat">

            <p>
                {{ __('messages.company_users') }}
            </p>

            <strong>
                {{ $companyUsersCount }}
                @if($company)
                    <span style="font-size:14px;color:#8A8178">
                        / {{ $company->max_users }}
                    </span>
                @endif
            </strong>

        </div>

    </div>


    {{-- الشركة والاشتراك --}}
    @if($company)

        <div class="company-box">

            <div>

                <h3>
                    {{ $company->name }}
                </h3>

                <p>
                    {{ $company->email ?: __('messages.no_registered_email') }}
                </p>

            </div>

            <div class="subscription">

                <span>
                    {{ __('messages.subscription') }}
                </span>

                @if($subscriptionDaysLeft === null)

                    <strong>
                        {{ __('messages.not_specified') }}
                    </strong>

                @elseif($subscriptionDaysLeft < 0)

                    <strong class="red">
                        {{ __('messages.expired') }}
                    </strong>

                @elseif($subscriptionDaysLeft == 0)

                    <strong style="color:#D97706">
                        {{ __('messages.expires_today') }}
                    </strong>

                @else

                    <strong>
                        {{ __('messages.days_remaining', ['days' => $subscriptionDaysLeft]) }}
                    </strong>

                @endif

            </div>

        </div>

    @endif


    {{-- آخر العمليات --}}
    <div class="section-grid">

        <div class="dashboard-card">

            <div class="card-head">

                <h2>
                    {{ __('messages.latest_receipts') }}
                </h2>

                <a href="/receipts">
                    {{ __('messages.view_all') }}
                </a>

            </div>

            <table class="simple-table">

                <thead>
                    <tr>
                        <th>{{ __('messages.voucher') }}</th>
                        <th>{{ __('messages.customer') }}</th>
                        <th>{{ __('messages.amount') }}</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($latestReceipts as $receipt)

                        <tr>

                            <td>
                                {{ $receipt->receipt_no }}
                            </td>

                            <td>
                                {{ $receipt->party?->name ?? '-' }}
                            </td>

                            <td class="green" style="font-weight:800">
                                {{ number_format($receipt->amount,0) }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="3" class="empty-state">
                                {{ __('messages.no_receipts') }}
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="dashboard-card">

            <div class="card-head">

                <h2>
                    {{ __('messages.latest_payments') }}
                </h2>

                <a href="/payments">
                    {{ __('messages.view_all') }}
                </a>

            </div>

            <table class="simple-table">

                <thead>
                    <tr>
                        <th>{{ __('messages.voucher') }}</th>
                        <th>{{ __('messages.supplier') }}</th>
                        <th>{{ __('messages.amount') }}</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($latestPayments as $payment)

                        <tr>

                            <td>
                                {{ $payment->payment_no }}
                            </td>

                            <td>
                                {{ $payment->party?->name ?? '-' }}
                            </td>

                            <td class="red" style="font-weight:800">
                                {{ number_format($payment->amount,0) }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="3" class="empty-state">
                                {{ __('messages.no_payments') }}
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
