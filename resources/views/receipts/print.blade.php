<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>سند قبض</title>@include('documents._styles')<style>@page{size:A5 landscape;margin:7mm}</style></head><body><main class="document voucher-document">
@include('documents._header',['companyId'=>$receipt->company_id,'documentTitle'=>'سند قبض'])
<table class="hero" dir="ltr"><tr><td class="hero-meta" dir="rtl">رقم السند<strong>{{ $receipt->receipt_no }}</strong></td><td dir="rtl" style="text-align:right"><div class="hero-title">@include('documents._bilingual-name',['name'=>$receipt->party?->name,'fallback'=>'سند قبض نقدي'])</div><div class="hero-subtitle">إيصال رسمي باستلام المبلغ المبين أدناه</div></td></tr></table>
<table class="voucher-grid"><tr>
<td class="voucher-amount"><div class="amount-label">المبلغ المستلم</div><div class="amount-value positive">{{ number_format($receipt->amount,2) }}</div><strong>{{ $companyCurrency }}</strong><div class="muted" style="margin-top:7px">تم استلام المبلغ بموجب هذا السند</div></td>
<td class="voucher-details"><table class="info-table" dir="ltr">
<tr><td>{{ $receipt->receipt_date?->format('Y-m-d') }}</td><td class="label" dir="rtl">التاريخ</td><td>{{ $receipt->receipt_no }}</td><td class="label" dir="rtl">رقم السند</td></tr>
<tr><td>{{ $receipt->party?->phone??'-' }}</td><td class="label" dir="rtl">الهاتف</td><td dir="rtl">@include('documents._bilingual-name',['name'=>$receipt->party?->name])</td><td class="label" dir="rtl">استلمنا من</td></tr>
<tr><td dir="rtl">{{ $receipt->cashbox?->name??'-' }}</td><td class="label" dir="rtl">الحساب المالي</td><td>{{ $receipt->status==='cancelled'?'ملغي':'فعال' }}</td><td class="label" dir="rtl">الحالة</td></tr>
</table><h2 class="section-title">البيان والملاحظات</h2><div class="notes">{{ $receipt->notes?:'لا توجد ملاحظات' }}</div></td>
</tr></table>
@include('documents._voucher_verification',['documentNumber'=>$receipt->receipt_no,'documentLabel'=>'سند القبض'])
@include('documents._footer')</main>@unless($pdfMode??false)<div class="actions"><button onclick="window.print()">طباعة</button><a href="/receipts/{{ $receipt->id }}/pdf">PDF</a><a href="/receipts/{{ $receipt->id }}/excel">Excel</a></div>@endunless</body></html>
