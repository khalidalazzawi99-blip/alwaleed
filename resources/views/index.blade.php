<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<title>{{ __('إدارة الزبائن') }}</title>

<style>
@font-face{
    font-family:'The Year of Handicrafts';
    src:url('{{ asset('fonts/the-year-of-handicrafts-regular.otf') }}') format('opentype');
    font-style:normal;
    font-weight:100 900;
    font-display:swap;
}

html,body,button,input,textarea,select{
    font-family:'The Year of Handicrafts',sans-serif;
}

body{
    background:#f1f3f6;
    padding:20px;
}

.container{
    width:900px;
    margin:auto;
    background:white;
    padding:30px;
    border-radius:15px;
}

input,textarea{
    width:100%;
    padding:12px;
    margin-top:10px;
    border:1px solid #ccc;
    border-radius:8px;
}

button{
    background:#0f172a;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:8px;
    cursor:pointer;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th,td{
    border:1px solid #ddd;
    padding:10px;
    text-align:center;
}

th{
    background:#0f172a;
    color:white;
}
</style>

</head>
<body>

<div class="container">

<h1>{{ __('إضافة زبون جديد') }}</h1>

<form method="POST" action="/customers">
@csrf

<input type="text" name="name" placeholder="{{ __('اسم الزبون') }}" required>

<input type="text" name="phone" placeholder="{{ __('رقم الهاتف') }}">

<input type="text" name="company_name" placeholder="{{ __('اسم الشركة') }}">

<input type="text" name="address" placeholder="{{ __('العنوان') }}">

<textarea name="notes" placeholder="{{ __('ملاحظات') }}"></textarea>

<br><br>

<button type="submit">{{ __('حفظ الزبون') }}</button>

</form>

<hr>

<h2>{{ __('الزبائن المسجلين') }}</h2>

<table>

<tr>
<th>ID</th>
<th>{{ __('الاسم') }}</th>
<th>{{ __('الهاتف') }}</th>
<th>{{ __('الشركة') }}</th>
<th>{{ __('العنوان') }}</th>
</tr>
<h3>{{ __('messages.customers_count', ['count' => count($customers)]) }}</h3>

@foreach($customers as $customer)

<tr>
<td>{{ $customer->id }}</td>
<td>{{ $customer->name }}</td>
<td>{{ $customer->phone }}</td>
<td>{{ $customer->company_name }}</td>
<td>{{ $customer->address }}</td>
</tr>

@endforeach

</table>

</div>

</body>
</html>
