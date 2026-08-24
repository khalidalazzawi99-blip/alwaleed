@extends('layouts.app')

@section('content')

<div class="topbar">
    <h1 class="page-title">{{ __('💰 الصندوق') }}</h1>
    <p style="color:#64748B">{{ __('متابعة رصيد الصندوق وحركة القبض والصرف') }}</p>
</div>

@if(session('success'))<div class="card" style="color:#166534;background:#ECFDF3">{{ session('success') }}</div>@endif

@if(auth()->user()->company?->hasFeature('multiple_cashboxes'))
<div class="card">
    <h2>إدارة الصناديق</h2>
    <form method="POST" action="/cashbox" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">@csrf
        <div><label>اسم الصندوق</label><input name="name" required></div>
        <div><label>الرصيد الافتتاحي</label><input type="number" step="0.01" name="balance" value="0"></div>
        <button>إضافة صندوق</button>
    </form>
    <table style="margin-top:18px"><thead><tr><th>الصندوق</th><th>الرصيد</th><th>الحالة</th><th>الإجراءات</th></tr></thead><tbody>
    @forelse($cashboxes as $box)<tr><form method="POST" action="/cashbox/{{ $box->id }}">@csrf @method('PUT')
        <td><input name="name" value="{{ $box->name }}" required></td><td>{{ number_format($box->balance,2) }} {{ $companyCurrency }}</td>
        <td><label><input type="checkbox" name="is_active" value="1" @checked($box->is_active)> فعال</label></td>
        <td><button>حفظ</button></form>@if($cashboxes->count()>1 && (float)$box->balance===0.0)<form method="POST" action="/cashbox/{{ $box->id }}" style="display:inline">@csrf @method('DELETE')<button style="background:#B91C1C">حذف</button></form>@endif</td>
    </tr>@empty<tr><td colspan="4">لا يوجد صندوق.</td></tr>@endforelse
    </tbody></table>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px">

    <div class="card">
        <h3>{{ __('الرصيد الحالي') }}</h3>
        <h1 style="color:#2563EB">
            {{ number_format($balance,2) }} {{ $companyCurrency }}
        </h1>
    </div>

    <div class="card">
        <h3>{{ __('إجمالي الداخل') }}</h3>
        <h1 style="color:#16A34A">
            {{ number_format($totalReceipts,2) }} {{ $companyCurrency }}
        </h1>
    </div>

    <div class="card">
        <h3>{{ __('إجمالي الخارج') }}</h3>
        <h1 style="color:#DC2626">
            {{ number_format($totalPayments,2) }} {{ $companyCurrency }}
        </h1>
    </div>

</div>

<div class="card">

    <h2>{{ __('حركة الصندوق') }}</h2>

    <table>

        <thead>
            <tr>
                <th>{{ __('النوع') }}</th>
                <th>{{ __('رقم الوصل') }}</th>
                <th>{{ __('الاسم') }}</th>
                <th>{{ __('المبلغ') }}</th>
                <th>{{ __('التاريخ') }}</th>
                <th>{{ __('الملاحظات') }}</th>
            </tr>
        </thead>

        <tbody>

        @foreach($receipts as $receipt)

            <tr>
                <td style="color:#16A34A;font-weight:800">
                    {{ __('قبض') }}
                </td>

                <td>{{ $receipt->receipt_no }}</td>

                <td>{{ $receipt->party?->name ?? '-' }}</td>

                <td style="color:#16A34A;font-weight:800">
                    +{{ number_format($receipt->amount,2) }}
                </td>

                <td>{{ $receipt->receipt_date }}</td>

                <td>{{ $receipt->notes }}</td>
            </tr>

        @endforeach

        @foreach($payments as $payment)

            <tr>
                <td style="color:#DC2626;font-weight:800">
                    {{ __('صرف') }}
                </td>

                <td>{{ $payment->payment_no }}</td>

                <td>{{ $payment->party?->name ?? '-' }}</td>

                <td style="color:#DC2626;font-weight:800">
                    -{{ number_format($payment->amount,2) }}
                </td>

                <td>{{ $payment->payment_date }}</td>

                <td>{{ $payment->notes }}</td>
            </tr>

        @endforeach

        </tbody>

    </table>

</div>

@endsection
