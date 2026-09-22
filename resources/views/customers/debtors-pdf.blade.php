<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>الزبائن أصحاب المبالغ المتبقية</title>
    @include('documents._styles')
</head>
<body>
<main class="document">
    @include('documents._header', ['companyId' => $companyId, 'documentTitle' => 'المبالغ المتبقية على الزبائن'])

    <table class="hero"><tr>
        <td><div class="hero-title">تقرير ديون الزبائن</div><div class="hero-subtitle">الزبائن الذين لديهم رصيد مستحق أكبر من صفر</div></td>
        <td class="hero-meta">تاريخ التقرير<strong>{{ now()->format('Y/m/d H:i') }}</strong></td>
    </tr></table>

    <table class="summary"><tr>
        <td>عدد الزبائن<span class="value">{{ number_format($customers->count()) }}</span></td>
        <td>إجمالي المبالغ المتبقية<span class="value negative">{{ number_format($totalOutstanding, 2) }} {{ $currency }}</span></td>
    </tr></table>

    <h2 class="section-title">تفاصيل المبالغ المستحقة</h2>
    <table class="data">
        <thead><tr><th style="width:10%">#</th><th style="width:60%">اسم الزبون</th><th style="width:30%">المبلغ المتبقي</th></tr></thead>
        <tbody>
        @forelse($customers as $index => $customer)
            <tr><td>{{ $index + 1 }}</td><td style="text-align:right;font-weight:700">{{ $customer->name }}</td><td class="negative" dir="ltr" style="font-weight:700">{{ number_format($customer->remaining_amount, 2) }} {{ $currency }}</td></tr>
        @empty
            <tr><td colspan="3">لا يوجد زبائن عليهم مبالغ متبقية</td></tr>
        @endforelse
        </tbody>
        @if($customers->isNotEmpty())
            <tfoot><tr><th colspan="2">الإجمالي</th><th class="negative" dir="ltr">{{ number_format($totalOutstanding, 2) }} {{ $currency }}</th></tr></tfoot>
        @endif
    </table>

    @include('documents._footer')
</main>
</body>
</html>
