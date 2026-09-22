<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>الزبائن أصحاب المبالغ المتبقية</title>
    @include('documents._styles')
    <style>
        @page { size: A4 portrait; margin: 10mm 10mm 15mm; }
        html, body { direction: rtl; }
        body { background: #fff; font-size: 10px; }
        .document { width: auto !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
        .top-gradient { margin: 0 0 8mm; }
        .hero, .summary, .debt-table { direction: ltr; }
        .hero td, .summary td, .debt-table th, .debt-table td { direction: rtl; }
        .hero-main { text-align: right; }
        .hero-meta { width: 30%; text-align: left; }
        .summary { border-spacing: 7px 0; margin-left: -7px; margin-right: -7px; }
        .debt-table { table-layout: fixed; }
        .debt-table .sequence { width: 10%; text-align: center; }
        .debt-table .customer { width: 58%; text-align: right; }
        .debt-table .amount { width: 32%; direction: ltr; text-align: left; white-space: nowrap; }
        .customer-name-part { display: block; line-height: 1.65; }
        .customer-name-part + .customer-name-part { color: #687386; font-size: 8px; }
    </style>
</head>
<body>
<main class="document">
    @include('documents._header', ['companyId' => $companyId, 'documentTitle' => 'المبالغ المتبقية على الزبائن'])

    <table class="hero"><tr>
        <td class="hero-meta">تاريخ التقرير<strong>{{ now()->format('Y/m/d H:i') }}</strong></td>
        <td class="hero-main"><div class="hero-title">تقرير ديون الزبائن</div><div class="hero-subtitle">الزبائن الذين لديهم رصيد مستحق أكبر من صفر</div></td>
    </tr></table>

    <table class="summary"><tr>
        <td>إجمالي المبالغ المتبقية<span class="value negative">{{ number_format($totalOutstanding, 2) }} {{ $currency }}</span></td>
        <td>عدد الزبائن<span class="value">{{ number_format($customers->count()) }}</span></td>
    </tr></table>

    <h2 class="section-title">تفاصيل المبالغ المستحقة</h2>
    <table class="data debt-table">
        <thead><tr><th class="amount">المبلغ المتبقي</th><th class="customer">اسم الزبون</th><th class="sequence">#</th></tr></thead>
        <tbody>
        @forelse($customers as $index => $customer)
            @php($nameParts = preg_split('/\s*\|\s*/u', $customer->name, -1, PREG_SPLIT_NO_EMPTY) ?: [$customer->name])
            <tr>
                <td class="amount negative" style="font-weight:700">{{ number_format($customer->remaining_amount, 2) }} {{ $currency }}</td>
                <td class="customer" style="font-weight:700">@foreach($nameParts as $namePart)<span class="customer-name-part">{{ $namePart }}</span>@endforeach</td>
                <td class="sequence">{{ $index + 1 }}</td>
            </tr>
        @empty
            <tr><td colspan="3">لا يوجد زبائن عليهم مبالغ متبقية</td></tr>
        @endforelse
        </tbody>
        @if($customers->isNotEmpty())
            <tfoot><tr><th class="amount negative">{{ number_format($totalOutstanding, 2) }} {{ $currency }}</th><th colspan="2">الإجمالي</th></tr></tfoot>
        @endif
    </table>
</main>
</body>
</html>
