<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>التحقق من السند | الوليد</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#f7f5f2;color:#25211d;font-family:Tajawal,sans-serif}.verify-card{width:min(620px,100%);overflow:hidden;border:1px solid #e8e1d8;border-radius:24px;background:#fff;box-shadow:0 18px 55px rgba(25,41,68,.12)}.bar{height:7px;background:linear-gradient(90deg,#d8bd91,#b49470,#536080,#192944)}.content{padding:32px}.brand{display:flex;align-items:center;gap:14px;margin-bottom:25px}.brand img{width:74px;height:74px;object-fit:contain}.brand h1{margin:0;color:#192944;font-size:23px}.brand p{margin:4px 0 0;color:#8a8178}.status{padding:16px;border-radius:14px;text-align:center;font-size:18px;font-weight:800}.active{color:#13795b;background:#e9f8f1}.cancelled{color:#b4232d;background:#fdecee}.invalid{color:#b4232d;background:#fdecee}.details{width:100%;margin-top:22px;border-collapse:collapse}.details td{padding:12px;border-bottom:1px solid #eee8e1}.details td:first-child{width:38%;color:#8a6d48;font-weight:700}.number{direction:ltr;unicode-bidi:isolate;font-weight:800}.foot{margin-top:22px;color:#8a8178;text-align:center;font-size:13px}
    </style>
</head>
<body><main class="verify-card"><div class="bar"></div><div class="content">
    <div class="brand">
        @if(($valid ?? false) && $document->company?->logo)<img src="{{ asset('storage/'.$document->company->logo) }}" alt="شعار الشركة">@endif
        <div><h1>نظام الوليد</h1><p>التحقق الإلكتروني من المستندات</p></div>
    </div>
    @if(!($valid ?? false))
        <div class="status invalid">تعذر التحقق من هذا السند</div>
        <p class="foot">الرابط غير صحيح أو لا يعود إلى مستند صادر من النظام.</p>
    @else
        <div class="status {{ $document->status === 'cancelled' ? 'cancelled' : 'active' }}">
            @if($document->status === 'cancelled') هذا السند ملغي
            @else {{ $type === 'receipt' ? 'سند القبض صحيح وفعال' : 'سند الصرف صحيح وفعال' }} @endif
        </div>
        <table class="details">
            <tr><td>اسم الشركة</td><td>{{ $document->company?->name }}</td></tr>
            <tr><td>نوع المستند</td><td>{{ $type === 'receipt' ? 'سند قبض' : 'سند صرف' }}</td></tr>
            <tr><td>رقم السند</td><td class="number">{{ $type === 'receipt' ? $document->receipt_no : $document->payment_no }}</td></tr>
            <tr><td>التاريخ</td><td>{{ ($type === 'receipt' ? $document->receipt_date : $document->payment_date)?->format('Y-m-d') }}</td></tr>
            <tr><td>الجهة</td><td>{{ $document->party?->name ?? '-' }}</td></tr>
            <tr><td>المبلغ</td><td>{{ number_format($document->amount, 2) }} {{ $currency }}</td></tr>
            <tr><td>الحالة</td><td>{{ $document->status === 'cancelled' ? 'ملغي' : 'فعال' }}</td></tr>
        </table>
        <p class="foot">تعرض هذه الصفحة بيانات التحقق العامة فقط ولا تحتوي على أي معرّفات داخلية.</p>
    @endif
</div></main></body></html>
