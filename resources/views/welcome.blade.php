<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>Al Waleed ERP</title>
    <style>
        @font-face {font-family:'The Year of Handicrafts';src:url('{{ asset('fonts/the-year-of-handicrafts-regular.otf') }}') format('opentype');font-style:normal;font-weight:100 900;font-display:swap;}
        html,body,button,input,textarea,select {font-family:'The Year of Handicrafts',sans-serif;}
        body {background:#f4f6f8; margin:0;}
        .header {background:#111827; color:white; padding:25px; text-align:center;}
        .cards {display:grid; grid-template-columns:repeat(4,1fr); gap:20px; padding:30px;}
        .card {background:white; padding:25px; border-radius:15px; box-shadow:0 5px 20px #ddd;}
        .card h3 {margin:0; color:#555;}
        .card p {font-size:28px; font-weight:bold; margin:15px 0 0;}
    </style>
</head>
<body>
    <div class="header">
        <h1>Al Waleed ERP</h1>
        <p>{{ __('نظام إدارة الحسابات والأعمال') }}</p>
    </div>

    <div class="cards">
        <div class="card"><h3>{{ __('الرصيد الكلي') }}</h3><p>0 IQD</p></div>
        <div class="card"><h3>{{ __('الإيرادات') }}</h3><p>0 IQD</p></div>
        <div class="card"><h3>{{ __('المصاريف') }}</h3><p>0 IQD</p></div>
        <div class="card"><h3>{{ __('عدد الحركات') }}</h3><p>0</p></div>
    </div>
</body>
</html>
