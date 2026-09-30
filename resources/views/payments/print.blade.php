<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>سند صرف</title>@include('documents._styles')<style>@page{size:A5 landscape;margin:7mm 7mm 14mm}</style></head><body><main class="document voucher-document">
@include('documents._header',['companyId'=>$payment->company_id,'documentTitle'=>'سند صرف'])
<table class="hero" dir="ltr"><tr><td class="hero-meta" dir="rtl">رقم السند<strong>{{ $payment->payment_no }}</strong></td><td dir="rtl" style="text-align:right"><div class="hero-title">@include('documents._bilingual-name',['name'=>$payment->party?->name,'fallback'=>'سند صرف نقدي'])</div><div class="hero-subtitle">إيصال رسمي بصرف المبلغ المبين أدناه</div></td></tr></table>
<table class="voucher-grid"><tr>
<td class="voucher-amount"><div class="amount-label">المبلغ المصروف</div><div class="amount-value negative">{{ number_format($payment->amount,2) }}</div><strong>{{ $companyCurrency }}</strong><div class="muted" style="margin-top:7px">تم صرف المبلغ بموجب هذا السند</div></td>
<td class="voucher-details"><table class="info-table" dir="ltr">
<tr><td>{{ $payment->payment_date?->format('Y-m-d') }}</td><td class="label" dir="rtl">التاريخ</td><td>{{ $payment->payment_no }}</td><td class="label" dir="rtl">رقم السند</td></tr>
<tr><td>{{ $payment->party?->phone??'-' }}</td><td class="label" dir="rtl">الهاتف</td><td dir="rtl">@include('documents._bilingual-name',['name'=>$payment->party?->name])</td><td class="label" dir="rtl">صُرف إلى</td></tr>
<tr><td dir="rtl">{{ $payment->cashbox?->name??'-' }}</td><td class="label" dir="rtl">الحساب المالي</td><td>{{ $payment->status==='cancelled'?'ملغي':'فعال' }}</td><td class="label" dir="rtl">الحالة</td></tr>
</table><h2 class="section-title">البيان والملاحظات</h2><div class="notes">{{ $payment->notes?:'لا توجد ملاحظات' }}</div></td>
</tr></table>
@include('documents._voucher_verification',['documentNumber'=>$payment->payment_no,'documentLabel'=>'سند الصرف'])
@include('documents._footer')</main>@unless($pdfMode??false)<div class="actions"><button onclick="window.print()">طباعة</button><a href="/payments/{{ $payment->id }}/pdf">PDF</a><a href="/payments/{{ $payment->id }}/excel">Excel</a></div>@endunless</body></html>
