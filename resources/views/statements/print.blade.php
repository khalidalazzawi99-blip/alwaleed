<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $statementTitle }}</title>
    @include('documents._styles')
    <style>
        .statement-document { direction: rtl; text-align: right; font-size: 14px; line-height: 1.65; }
        /* DomPDF lays columns out from the left; the Arabic starting column goes last. */
        .statement-document table { direction: ltr; }
        .statement-document td, .statement-document th { direction: rtl; text-align: right; }
        .statement-document .document-header .title-cell { text-align: right; width: 58%; }
        .statement-document .document-header .brand-cell { text-align: right; width: 42%; white-space: normal; }
        .statement-document .document-title { font-size: 26px; }
        .statement-document .muted, .statement-document .hero-subtitle { font-size: 12px; }
        .statement-document .hero-main { width: 68%; text-align: right; }
        .statement-document .hero-title { font-size: 21px; }
        .statement-document .hero-meta { width: 32%; text-align: right; font-size: 12px; }
        .statement-document .hero-meta strong { font-size: 13px; }
        .statement-document .bilingual-name { display: block; }
        .statement-document .bilingual-name-ar, .statement-document .bilingual-name-en { text-align: right; }
        .statement-document .summary td { font-size: 12px; padding: 9px 5px; text-align: right; }
        .statement-document .summary .value { font-size: 14px; text-align: right; }
        .statement-document .section-title { font-size: 17px; text-align: right; }
        .statement-document .data th, .statement-document .data td { font-size: 12px; padding: 8px 4px; text-align: right; }
        .statement-document .data .sequence { width: 4%; }
        .statement-document .data .date { width: 12%; font-size: 11px; direction: ltr; white-space: nowrap; }
        .statement-document .data .reference { width: 12%; }
        .statement-document .data .movement-type { width: 10%; }
        .statement-document .data .amount { width: 12%; direction: ltr; font-size: 12px; }
        .statement-document .data .running-balance { width: 13%; }
        .statement-document .data .movement-notes { width: 13%; direction: rtl; }
        .statement-document .data td[colspan] { text-align: right; }
        .statement-document .document-footer { font-size: 10px; }
    </style>
</head>
<body>
<main class="document statement-document">
    @include('documents._header', ['companyId' => $companyId, 'documentTitle' => $statementTitle, 'titleOnRight' => true])
    <table class="hero"><tr>
        <td class="hero-meta">فترة الكشف<strong>{{ $from ?: 'جميع التواريخ' }} — {{ $to ?: 'اليوم' }}</strong></td>
        <td class="hero-main"><div class="hero-title">@include('documents._bilingual-name', ['name' => $party->name])</div><div class="hero-subtitle">{{ $party->company_name ?: 'جميع الحركات المالية والرصيد التراكمي' }}</div></td>
    </tr></table>
    <table class="summary"><tr>
        <td>عدد الحركات<span class="value">{{ $movementsCount }}</span></td>
        <td>الرصيد الحالي<span class="value {{ $balance < 0 ? 'negative' : '' }}">{{ number_format($balance, 2) }} {{ $currency }}</span></td>
        <td>إجمالي المصروف<span class="value negative">{{ number_format($totalPaid, 2) }} {{ $currency }}</span></td>
        <td>إجمالي المقبوض<span class="value positive">{{ number_format($totalReceived, 2) }} {{ $currency }}</span></td>
        <td>إجمالي الفواتير<span class="value">{{ number_format($totalInvoiced, 2) }} {{ $currency }}</span></td>
    </tr></table>
    <table class="info-table">
        <tr><td>{{ $party->address ?: '-' }}</td><td class="label">العنوان</td><td dir="ltr">{{ $party->phone ?: '-' }}</td><td class="label">رقم الهاتف</td></tr>
        <tr><td colspan="3">{{ $party->notes ?: 'لا توجد ملاحظات' }}</td><td class="label">ملاحظات</td></tr>
    </table>
    <h2 class="section-title">سجل الحركات</h2>
    <table class="data">
        <thead><tr>
            <th class="movement-notes">الملاحظات</th><th class="amount running-balance">الرصيد التراكمي</th>
            <th class="amount">مدفوع</th><th class="amount">مقبوض</th><th class="amount">قيمة الفاتورة</th>
            <th class="movement-type">نوع الحركة</th><th class="reference">رقم المرجع</th><th class="date">التاريخ</th><th class="sequence">#</th>
        </tr></thead>
        <tbody>
        @if(isset($openingBalance) && $from)
            <tr><td></td><td class="amount">{{ number_format($openingBalance, 2) }}</td><td colspan="7">{{ __('messages.period_opening_balance') }}</td></tr>
        @endif
        @forelse($movements as $i => $m)
            <tr>
                <td class="movement-notes">{{ $m->notes ?: '-' }}</td><td class="amount">{{ number_format($m->balance, 2) }}</td>
                <td class="amount negative">{{ number_format($m->paid, 2) }}</td><td class="amount positive">{{ number_format($m->received, 2) }}</td><td class="amount">{{ number_format($m->invoiced, 2) }}</td>
                <td class="movement-type">{{ $m->type }}</td><td class="reference">{{ $m->number }}</td><td class="date">{{ $m->date }}</td><td class="sequence">{{ $i + 1 }}</td>
            </tr>
        @empty
            <tr><td colspan="9">لا توجد حركات</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr>
            <th></th><th class="amount">{{ number_format($balance, 2) }}</th>
            <th class="amount negative">{{ number_format($totalPaid, 2) }}</th><th class="amount positive">{{ number_format($totalReceived, 2) }}</th><th class="amount">{{ number_format($totalInvoiced, 2) }}</th><th colspan="4">المجموع</th>
        </tr></tfoot>
    </table>
    @include('documents._footer')
</main>
@unless($pdfMode ?? false)
    <div class="actions"><button onclick="window.print()">طباعة كشف الحساب</button></div>
@endunless
</body>
</html>
