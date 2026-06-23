<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إدارة الزبائن</title>

<style>
body{
    font-family:Tahoma;
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

<h1>إضافة زبون جديد</h1>

<form method="POST" action="/customers">
@csrf

<input type="text" name="name" placeholder="اسم الزبون" required>

<input type="text" name="phone" placeholder="رقم الهاتف">

<input type="text" name="company_name" placeholder="اسم الشركة">

<input type="text" name="address" placeholder="العنوان">

<textarea name="notes" placeholder="ملاحظات"></textarea>

<br><br>

<button type="submit">حفظ الزبون</button>

</form>

<hr>

<h2>الزبائن المسجلين</h2>

<table>

<tr>
<th>ID</th>
<th>الاسم</th>
<th>الهاتف</th>
<th>الشركة</th>
<th>العنوان</th>
</tr>
<h3>عدد الزبائن: {{ count($customers) }}</h3>

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