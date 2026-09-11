<!DOCTYPE html>
<html lang="ar" dir="rtl"><head><meta charset="utf-8">
<style>
@page{margin:24px 25px 35px}body{font-family:"DejaVu Sans",sans-serif;font-size:10px;color:#433b32;direction:rtl}
h1{font-size:22px;margin:4px 0}h2{font-size:16px;margin:4px 0;color:#967b57}.meta{color:#786f65;margin:8px 0}
table{width:100%;border-collapse:collapse;table-layout:fixed}thead{display:table-header-group}tr{page-break-inside:avoid}
th{background:#eee7dd;padding:8px 5px}td{padding:7px 5px;border-bottom:1px solid #e8e1d8;text-align:center;word-wrap:break-word}
.summary td{background:#faf7f2;padding:12px 4px}.summary{margin:18px 0}.summary strong{display:block;margin-top:5px}
.total{text-align:center;background:#eee7dd;padding:12px;margin-top:12px;page-break-inside:avoid}.logo{max-width:90px;max-height:45px;float:left}
</style></head><body>
@if($logoPath)<img class="logo" src="data:{{ mime_content_type($logoPath) }};base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="">@endif
<h2>أضواء سيبار</h2><h1>الحسابات اليومية</h1>
<div class="meta">الفترة: {{ $period }} | {{ $currency }}</div>
<div class="meta">تاريخ إنشاء التقرير: {{ $generatedAt->format('Y-m-d H:i') }}</div>
<table class="summary"><tr>
<td>إجمالي المصروف<strong>{{ number_format($summary['total'], 2) }} {{ $currency }}</strong></td>
<td>عدد الحركات<strong>{{ $summary['count'] }}</strong></td>
<td>متوسط المصروف<strong>{{ number_format($summary['average'], 2) }} {{ $currency }}</strong></td>
<td>أعلى شخص صرف<strong>{{ $summary['top_person']?->party?->name ?? '—' }}</strong><span>{{ number_format($summary['top_person']?->total ?? 0, 2) }} {{ $currency }}</span></td>
</tr></table>
<table><thead><tr><th style="width:14%">التاريخ</th><th style="width:12%">اليوم</th><th style="width:19%">المبلغ</th><th style="width:17%">الجهة / الشخص</th><th style="width:30%">الملاحظات</th><th style="width:8%">الشهر</th></tr></thead><tbody>
@forelse($expenses as $expense)
<tr><td>{{ $expense->expense_date->format('Y-m-d') }}</td><td>{{ $expense->expense_date->locale('ar')->translatedFormat('l') }}</td><td>{{ number_format($expense->amount, 2) }} {{ $currency }}</td><td>{{ $expense->party->name }}</td><td>{{ $expense->notes }}</td><td>{{ $expense->expense_date->month }}</td></tr>
@empty
<tr><td colspan="6">لا توجد حركات مالية لشركة سيبار ضمن الفترة المحددة</td></tr>
@endforelse
</tbody></table>
<div class="total">الإجمالي: {{ number_format($summary['total'], 2) }} {{ $currency }}</div>
</body></html>
