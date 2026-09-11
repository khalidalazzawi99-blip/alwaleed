<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<style>
@page { margin: 24px 30px 42px; }
* { box-sizing: border-box; }
body { margin: 0; font-family: "DejaVu Sans", sans-serif; font-size: 9px; color: #3f382f; direction: rtl; }
.header { width: 100%; border-collapse: collapse; margin-bottom: 13px; }
.header td { border: 0; padding: 0; vertical-align: middle; }
.brand { width: 64%; text-align: right; }
.brand-line { width: 44px; height: 4px; margin: 0 0 9px auto; background: #b59669; border-radius: 4px; }
.company { margin: 0 0 3px; color: #9a7d56; font-size: 11px; font-weight: bold; }
h1 { margin: 0; color: #322c25; font-size: 24px; font-weight: bold; }
.subtitle { margin-top: 5px; color: #8b8175; font-size: 9px; }
.logo-cell { width: 36%; text-align: left; }
.logo { max-width: 115px; max-height: 58px; }
.logo-fallback { display: inline-block; padding: 12px 18px; border: 1px solid #e7ded1; border-radius: 8px; color: #a48761; font-size: 14px; font-weight: bold; }
.report-meta { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 13px; background: #f7f3ed; border: 1px solid #e8dfd3; }
.report-meta td { padding: 9px 12px; border: 0; color: #6f6559; text-align: right; }
.report-meta .label { color: #a0835d; font-weight: bold; }
.report-meta .generated { width: 34%; text-align: left; direction: rtl; }
.summary { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 15px; }
.summary td { width: 20%; height: 62px; padding: 9px 10px; vertical-align: top; background: #fbfaf7; border: 1px solid #ece5db; border-radius: 7px; text-align: right; }
.summary td.primary { background: #b59669; border-color: #b59669; color: #fff; }
.summary .title { display: block; margin-bottom: 7px; color: #8d8173; font-size: 8px; }
.summary .primary .title { color: #f6eee4; }
.summary strong { display: block; color: #3d352c; font-size: 14px; line-height: 1.35; direction: ltr; unicode-bidi: embed; text-align: right; }
.summary .primary strong { color: #fff; font-size: 15px; }
.summary small { display: block; margin-top: 3px; color: #9a8f82; font-size: 7px; }
.summary .person strong { direction: rtl; }
.section-title { margin: 0 0 7px; padding-right: 8px; border-right: 3px solid #b59669; color: #4b4238; font-size: 12px; }
.ledger { width: 100%; border-collapse: collapse; table-layout: fixed; }
.ledger thead { display: table-header-group; }
.ledger tr { page-break-inside: avoid; }
.ledger th { padding: 9px 6px; background: #4e463d; color: #fff; border-left: 1px solid #6b6258; font-size: 8px; font-weight: bold; text-align: center; }
.ledger th:first-child { border-radius: 0 6px 6px 0; }
.ledger th:last-child { border-radius: 6px 0 0 6px; border-left: 0; }
.ledger td { padding: 8px 6px; border-bottom: 1px solid #ebe5dc; text-align: center; vertical-align: middle; word-wrap: break-word; }
.ledger tbody tr:nth-child(even) td { background: #faf8f4; }
.ledger .date { direction: ltr; white-space: nowrap; }
.ledger .money { direction: ltr; unicode-bidi: embed; white-space: nowrap; color: #5b4933; font-weight: bold; }
.ledger .notes { text-align: right; color: #685f55; line-height: 1.5; }
.empty { padding: 28px !important; color: #998c7c; background: #faf8f4; }
.grand-total { width: 100%; margin-top: 12px; border-collapse: collapse; page-break-inside: avoid; }
.grand-total td { padding: 12px 15px; border: 1px solid #ddcfbc; background: #f3ece2; }
.grand-total .total-label { width: 62%; color: #7b6d5d; font-size: 11px; font-weight: bold; text-align: right; }
.grand-total .total-value { width: 38%; color: #4c3d2b; font-size: 17px; font-weight: bold; text-align: left; direction: ltr; unicode-bidi: embed; }
</style>
</head>
<body>
@php($money = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ','), '0'), '.').' '.$currency)
<table class="header"><tr>
<td class="brand">
    <div class="brand-line"></div>
    <div class="company">أضواء سيبار</div>
    <h1>الحسابات اليومية</h1>
    <div class="subtitle">تقرير المصروفات والحركات المالية اليومية</div>
</td>
<td class="logo-cell">
    @if($logoPath)
        <img class="logo" src="data:{{ mime_content_type($logoPath) }};base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="">
    @else
        <span class="logo-fallback">SIPPAR LIGHTS</span>
    @endif
</td>
</tr></table>

<table class="report-meta"><tr>
<td><span class="label">الفترة:</span> {{ $period }} &nbsp; | &nbsp; <span class="label">العملة:</span> {{ $currency }}</td>
<td class="generated"><span class="label">تاريخ التقرير:</span> {{ $generatedAt->format('Y-m-d H:i') }}</td>
</tr></table>

<table class="summary"><tr>
<td class="primary"><span class="title">إجمالي المصروف</span><strong>{{ $money($summary['total']) }}</strong><small>ضمن النتائج المحددة</small></td>
<td><span class="title">عدد الحركات</span><strong>{{ number_format($summary['count']) }}</strong><small>حركة مالية</small></td>
<td><span class="title">متوسط المصروف</span><strong>{{ $money($summary['average']) }}</strong><small>لكل حركة</small></td>
<td class="person"><span class="title">أعلى شخص صرف</span><strong>{{ $summary['top_person']?->party?->name ?? '—' }}</strong><small>{{ $money($summary['top_person']?->total ?? 0) }}</small></td>
<td><span class="title">أعلى شهر صرف</span><strong>{{ $summary['top_month']['name'] ?? '—' }}</strong><small>{{ $money($summary['top_month']['total'] ?? 0) }}</small></td>
</tr></table>

<h2 class="section-title">تفاصيل الحركات اليومية</h2>
<table class="ledger">
<thead><tr>
<th style="width:14%">التاريخ</th>
<th style="width:11%">اليوم</th>
<th style="width:18%">المبلغ</th>
<th style="width:17%">الجهة / الشخص</th>
<th style="width:32%">الملاحظات</th>
<th style="width:8%">الشهر</th>
</tr></thead>
<tbody>
@forelse($expenses as $expense)
<tr>
<td class="date">{{ $expense->expense_date->format('Y-m-d') }}</td>
<td>{{ $expense->expense_date->locale('ar')->translatedFormat('l') }}</td>
<td class="money">{{ $money($expense->amount) }}</td>
<td>{{ $expense->party->name }}</td>
<td class="notes">{{ $expense->notes ?: '—' }}</td>
<td>{{ $expense->expense_date->month }}</td>
</tr>
@empty
<tr><td colspan="6" class="empty">لا توجد حركات مالية لشركة سيبار ضمن الفترة المحددة</td></tr>
@endforelse
</tbody>
</table>

<table class="grand-total"><tr>
<td class="total-label">الإجمالي النهائي للمصروفات</td>
<td class="total-value">{{ $money($summary['total']) }}</td>
</tr></table>
</body>
</html>
