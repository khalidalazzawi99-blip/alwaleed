<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>Al Waleed ERP</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box}

body{
margin:0;
font-family:Tajawal;
background:#F9F8F6;
color:#1F1F1F
}

.app{display:flex;min-height:100vh}

.sidebar{
width:280px;
height:100vh;
position:fixed;
right:0;
top:0;
background:#EFE9E3;
color:#1F1F1F;
padding:24px;
border-left:1px solid #E8E6E3;
box-shadow:-10px 0 30px rgba(0,0,0,.04)
}

.brand{
background:#F5F1EB;
padding:20px;
border-radius:22px;
text-align:center;
margin-bottom:25px
}

.brand p{
margin:0;
color:#8A8178;
font-size:13px
}

.menu a{
display:block;
color:#1F1F1F;
text-decoration:none;
padding:14px 16px;
border-radius:14px;
margin-bottom:8px;
font-weight:600;
transition:.3s;
}

.menu a:hover{
background:#F5F1EB;
color:#CDBA9E;
transform:translateX(-4px);
}

.menu a.active{
background:#F5F1EB;
color:#CDBA9E;
border-right:4px solid #CDBA9E;
}

.content{
margin-right:280px;
width:calc(100% - 280px);
padding:30px
}

.topbar{
background:#FFFFFF;
padding:24px;
border-radius:24px;
box-shadow:0 10px 30px rgba(0,0,0,.05);
margin-bottom:24px;
border:1px solid #E8E6E3
}

.page-title{
margin:0;
font-size:30px;
font-weight:800;
color:#1F1F1F
}

.card{
background:#FFFFFF;
border-radius:24px;
padding:24px;
box-shadow:0 10px 30px rgba(0,0,0,.05);
margin-bottom:22px;
border:1px solid #E8E6E3
}

input,textarea,select{
width:100%;
padding:14px 16px;
border:1px solid #DDD7D1;
border-radius:14px;
background:#F8FAFC;
font-family:Tajawal;
font-size:15px
}

input:focus,textarea:focus,select:focus{
outline:none;
border-color:#CDBA9E;
background:#FFFFFF
}

button,.btn{
background:#CDBA9E;
color:white;
border:0;
padding:13px 22px;
border-radius:14px;
font-weight:700;
cursor:pointer;
text-decoration:none;
display:inline-block
}

button:hover,.btn:hover{
background:#BFA98A
}

.danger{background:#BFA98A}
.success{background:#CDBA9E}

table{
width:100%;
border-collapse:separate;
border-spacing:0 10px
}

th{
color:#8A8178;
text-align:right;
padding:10px
}

td{
background:#F8FAFC;
padding:14px
}

td:first-child{border-radius:0 14px 14px 0}
td:last-child{border-radius:14px 0 0 14px}
</style>

</head>

<body>

<div class="app">

<aside class="sidebar">

<div class="brand">
    <img src="/logo.png"
         style="width:190px;height:auto;display:block;margin:0 auto 12px auto;">
    <p>Accounts & Business</p>
</div>

<nav class="menu">

    <a href="/dashboard">لوحة التحكم</a>

    @if(auth()->user()->role == 'admin' || auth()->user()->role == 'accountant')
        <a href="/cashbox">الصندوق</a>
        <a href="/receipts">سندات القبض</a>
        <a href="/payments">سندات الصرف</a>
        <a href="/reports">التقارير</a>
    @endif

    @if(auth()->user()->role == 'admin' || auth()->user()->role == 'data_entry')
        <a href="/customers">الزبائن</a>
        <a href="/suppliers">الموردين</a>
    @endif

    @if(auth()->user()->role == 'admin')
        <a href="/users">إدارة الموظفين</a>
        <a href="/activity-logs">سجل النشاطات</a>
        <a href="/backup">نسخة احتياطية</a>
    @endif

    <form method="POST" action="/logout">
        @csrf
        <button style="width:100%;margin-top:15px">
            تسجيل الخروج
        </button>
    </form>

</nav>

</aside>

<main class="content">
    @yield('content')
</main>

</div>

</body>
</html>