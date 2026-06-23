<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>كشف حساب مورد</title>

<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box}
body{margin:0;font-family:'Tajawal',sans-serif;background:#F6F4F1;color:#1F1F1F}
.page{width:210mm;min-height:297mm;margin:20px auto;background:white;padding:22mm 16mm}
.header{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #CDBA9E;padding-bottom:18px;margin-bottom:24px}
.logo img{width:150px}
.title{text-align:left}
.title h1{margin:0;font-size:30px}
.title p{margin:6px 0 0;color:#8A8178}

.info{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:12px;
margin-bottom:24px;
}

.box{
background:#FAFAFA;
border:1px solid #ECE7E1;
border-radius:16px;
padding:15px;
}

.box span{
color:#8A8178;
font-size:13px;
}

.box h3{
margin:8px 0 0;
font-size:18px;
}

.summary{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:12px;
margin-bottom:26px;
}

.summary .box h2{
margin:8px 0 0;
font-size:24px;
color:#B91C1C;
}

.section-title{
font-size:22px;
font-weight:800;
margin:20px 0 12px;
}

table{
width:100%;
border-collapse:collapse;
font-size:14px;
}

th{
background:#F5F1EB;
color:#6B5D4B;
padding:10px;
border:1px solid #E6DED4;
}

td{
padding:10px;
border:1px solid #EDE7DF;
vertical-align:top;
}

tr:nth-child(even) td{
background:#FAFAFA;
}

.amount{
color:#B91C1C;
font-weight:800;
white-space:nowrap;
}

.notes{
max-width:300px;
line-height:1.7;
}

.footer{
margin-top:35px;
padding-top:14px;
border-top:1px solid #ECE7E1;
display:flex;
justify-content:space-between;
color:#8A8178;
font-size:13px;
}

.print-btn{
text-align:center;
margin:20px;
}

button{
background:#CDBA9E;
color:white;
border:0;
padding:14px 34px;
border-radius:12px;
font-family:'Tajawal',sans-serif;
font-weight:800;
cursor:pointer;
}

@media print{
body{background:white}
.page{
width:100%;
min-height:auto;
margin:0;
padding:14mm;
}
.print-btn{display:none}
}
</style>
</head>

<body>

<div class="page">

<div class="header">

<div class="logo">
<img src="/logo.png">
</div>

<div class="title">
<h1>كشف حساب مورد</h1>
<p>Al Waleed ERP</p>
<p>تاريخ الطباعة: {{ date('Y-m-d') }}</p>
</div>

</div>

<div class="info">

<div class="box">
<span>اسم المورد</span>
<h3>{{ $supplier->name }}</h3>
</div>

<div class="box">
<span>رقم الهاتف</span>
<h3>{{ $supplier->phone ?? '-' }}</h3>
</div>

<div class="box">
<span>الشركة</span>
<h3>{{ $supplier->company_name ?? '-' }}</h3>
</div>

</div>

<div class="summary">

<div class="box">
<span>عدد سندات الصرف</span>
<h2>{{ $paymentsCount }}</h2>
</div>

<div class="box">
<span>إجمالي المدفوعات</span>
<h2>{{ number_format($totalPayments,2) }} IQD</h2>
</div>

</div>

<div class="section-title">
تفاصيل السندات
</div>

<table>

<thead>
<tr>
<th>رقم الوصل</th>
<th>التاريخ</th>
<th>المبلغ</th>
<th>الملاحظات</th>
</tr>
</thead>

<tbody>

@foreach($payments as $payment)

<tr>
<td>{{ $payment->payment_no }}</td>
<td>{{ $payment->payment_date }}</td>
<td class="amount">{{ number_format($payment->amount,2) }}</td>
<td class="notes">{{ $payment->notes ?? '-' }}</td>
</tr>

@endforeach

</tbody>

</table>

<div class="footer">
<span>Al Waleed ERP</span>
<span>Accounts & Business Management System</span>
</div>

</div>

<div class="print-btn">
<button onclick="window.print()">طباعة / حفظ PDF</button>
</div>

</body>
</html>