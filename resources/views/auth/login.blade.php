<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>تسجيل الدخول | Al Waleed </title>

<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Tajawal',sans-serif;
}

body{
background:#F7F5F2;
height:100vh;
display:flex;
justify-content:center;
align-items:center;
}

.login-box{
width:450px;
background:#fff;
padding:45px;
border-radius:24px;
box-shadow:0 15px 50px rgba(0,0,0,.08);
}

.logo{
text-align:center;
margin-bottom:30px;
}

.logo h1{
font-size:38px;
color:#CDBA9E;
font-weight:800;
}

.logo p{
color:#8A8178;
margin-top:6px;
}

label{
display:block;
margin-bottom:8px;
font-weight:700;
}

input{
width:100%;
padding:14px;
border:1px solid #E8E1D8;
border-radius:12px;
margin-bottom:20px;
font-size:15px;
}

input:focus{
outline:none;
border-color:#CDBA9E;
}

button{
width:100%;
padding:15px;
border:none;
background:#CDBA9E;
color:white;
font-size:16px;
font-weight:800;
border-radius:12px;
cursor:pointer;
}

button:hover{
opacity:.9;
}

.error{
background:#FEE2E2;
color:#991B1B;
padding:12px;
border-radius:10px;
margin-bottom:15px;
}

</style>
</head>

<body>

<div class="login-box">

<div class="logo">
<h1>Al Waleed</h1>
<p>نظام إدارة الحسابات والأعمال</p>
</div>

@if($errors->any())
<div class="error">
{{ $errors->first() }}
</div>
@endif

<form method="POST" action="/login">

@csrf

<label>البريد الإلكتروني</label>

<input
type="email"
name="email"
required>

<label>كلمة المرور</label>

<input
type="password"
name="password"
required>

<button type="submit">
تسجيل الدخول
</button>

</form>

</div>

</body>
</html>