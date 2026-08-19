<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<title>{{ __('سند قبض') }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Tajawal',sans-serif;
    background:#F6F4F1;
    padding:40px;
    color:#1F1F1F;
}

.receipt{
    width:900px;
    margin:auto;
    background:white;
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,.08);
}

.header{
    background:linear-gradient(135deg,#CDBA9E,#BFA98A);
    padding:35px;
    text-align:center;
}

.header img{
    width:180px;
    background:white;
    padding:15px;
    border-radius:20px;
}

.title{
    color:white;
    font-size:34px;
    font-weight:800;
    margin-top:20px;
}

.content{
    padding:35px;
}

.info{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.box{
    background:#FAFAFA;
    border:1px solid #ECE7E1;
    border-radius:16px;
    padding:18px;
    font-size:17px;
}

.amount{
    margin:30px 0;
    padding:25px;
    border-radius:18px;
    background:#F0FDF4;
    text-align:center;
    font-size:38px;
    font-weight:800;
    color:#15803D;
}

.notes{
    background:#FAFAFA;
    border:1px solid #ECE7E1;
    border-radius:16px;
    padding:20px;
    min-height:120px;
}

.signatures{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:40px;
    margin-top:60px;
}

.sign{
    text-align:center;
    padding-top:15px;
    border-top:2px solid #D8CBB9;
}

.print-btn{
    text-align:center;
    margin-top:25px;
}

.print-btn button{
    background:#CDBA9E;
    color:white;
    border:0;
    font-family:'Tajawal',sans-serif;
    font-weight:700;
    border-radius:14px;
    padding:14px 30px;
    cursor:pointer;
}

@media print{
    body{
        background:white;
        padding:0;
    }

    .print-btn{
        display:none;
    }

    .receipt{
        width:100%;
        box-shadow:none;
        border-radius:0;
    }
}
</style>
</head>

<body>

<div class="receipt">

    <div class="header">
        <img src="/logo.png" alt="Al Waleed">
        <div class="title">{{ __('سند قبض') }}</div>
    </div>

    <div class="content">

        <div class="info">
            <div class="box">
                <strong>{{ __('رقم الوصل:') }}</strong>
                {{ $receipt->receipt_no }}
            </div>

            <div class="box">
                <strong>{{ __('التاريخ:') }}</strong>
                {{ $receipt->receipt_date }}
            </div>

            <div class="box">
                <strong>{{ __('messages.party') }}:</strong>
                {{ $receipt->party?->name ?? '-' }}
            </div>

            <div class="box">
                <strong>{{ __('رقم الهاتف:') }}</strong>
                {{ $receipt->party?->phone ?? '-' }}
            </div>
        </div>

        <div class="amount">
            {{ number_format($receipt->amount,2) }} IQD
        </div>

        <div>
            <strong>{{ __('الملاحظات:') }}</strong>

            <div class="notes">
            {{ $receipt->notes ?? __('لا توجد ملاحظات') }}
            </div>
        </div>

        <div class="signatures">
            <div class="sign">{{ __('توقيع المحاسب') }}</div>
            <div class="sign">{{ __('توقيع المستلم') }}</div>
        </div>

    </div>

</div>

<div class="print-btn">
    <button onclick="window.print()">{{ __('طباعة السند') }}</button>
    <a href="/receipts/{{ $receipt->id }}/pdf" style="display:inline-block;background:#26344d;color:#fff;padding:14px 28px;border-radius:12px;text-decoration:none;font-weight:800">PDF</a>
    <a href="/receipts/{{ $receipt->id }}/excel" style="display:inline-block;background:#15803d;color:#fff;padding:14px 28px;border-radius:12px;text-decoration:none;font-weight:800">Excel</a>
</div>

</body>
</html>
