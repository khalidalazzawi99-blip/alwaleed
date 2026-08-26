<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script>
(function(){
    const saved = localStorage.getItem('alwaleed-theme');
    document.documentElement.setAttribute('data-theme', saved === 'dark' ? 'dark' : 'light');
})();
</script>

<title>{{ __('تسجيل الدخول | Al Waleed') }} </title>

<style>

@font-face{
font-family:'The Year of Handicrafts';
src:url('{{ asset('fonts/the-year-of-handicrafts-regular.otf') }}') format('opentype');
font-style:normal;
font-weight:100 900;
font-display:swap;
}

html,
body,
button,
input,
textarea,
select,
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'The Year of Handicrafts',sans-serif;
}

body{
background:#F7F5F2;
color:#25211D;
min-height:100vh;
display:flex;
justify-content:center;
align-items:center;
padding:24px;
transition:background .25s ease,color .25s ease;
}

.login-box{
width:450px;
background:#fff;
padding:45px;
border-radius:24px;
box-shadow:0 15px 50px rgba(0,0,0,.08);
transition:background .25s ease,box-shadow .25s ease,border-color .25s ease;
border:1px solid transparent;
}

.logo{
text-align:center;
margin-bottom:30px;
perspective:900px;
}

.logo-stage{
position:relative;
width:330px;
height:205px;
margin:0 auto 14px;
display:grid;
place-items:center;
animation:logoArrival 1.35s cubic-bezier(.16,1,.3,1) both;
}

.logo-mark{
position:relative;
width:330px;
height:205px;
overflow:hidden;
border-radius:28px;
background:transparent;
box-shadow:none;
transform:translateZ(0);
}

.logo-mark img{
position:absolute;
top:0;
left:50%;
width:360px;
max-width:none;
height:auto;
display:block;
transform:translate(-50%,-86px);
}

.logo-dark{display:none!important}
html[data-theme="dark"] .logo-light{display:none!important}
html[data-theme="dark"] .logo-dark{display:block!important}

.logo h1{
font-size:38px;
color:#CDBA9E;
font-weight:800;
opacity:0;
animation:titleReveal .7s ease-out .72s forwards;
}

.logo p{
color:#8A8178;
margin-top:6px;
opacity:0;
animation:titleReveal .7s ease-out .9s forwards;
}

@keyframes logoArrival{
0%{opacity:0;transform:translateY(-55px) rotateX(-55deg) rotateZ(-10deg) scale(.62);filter:blur(8px)}
58%{opacity:1;transform:translateY(6px) rotateX(5deg) rotateZ(2deg) scale(1.06);filter:blur(0)}
78%{transform:translateY(-3px) rotateX(-2deg) rotateZ(-1deg) scale(.99)}
100%{opacity:1;transform:translateY(0) rotateX(0) rotateZ(0) scale(1);filter:blur(0)}
}

@keyframes titleReveal{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

@media(prefers-reduced-motion:reduce){
.logo-stage,.logo h1,.logo p{animation:none!important;opacity:1!important;transform:none!important}
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
background:#fff;
color:#25211D;
transition:background .25s ease,border-color .25s ease,color .25s ease;
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

.login-controls{
position:fixed;
top:20px;
inset-inline-end:20px;
display:flex;
gap:6px;
z-index:20;
}

.language-switch{
display:flex;
gap:6px;
background:#fff;
padding:6px;
border-radius:12px;
box-shadow:0 8px 25px rgba(0,0,0,.08);
transition:background .25s ease,border-color .25s ease;
border:1px solid transparent;
}

.language-switch a{
color:#8A8178;
text-decoration:none;
font-weight:800;
padding:7px 10px;
border-radius:8px;
}

.language-switch a.active{
background:#CDBA9E;
color:#fff;
}

.theme-switch{
width:44px;
height:44px;
padding:0;
display:grid;
place-items:center;
border:1px solid #E8E1D8;
background:#fff;
color:#8A8178;
border-radius:12px;
box-shadow:0 8px 25px rgba(0,0,0,.08);
font-size:20px;
line-height:1;
}

html[data-theme="dark"] body{background:#121A2B;color:#F5F1EA}
html[data-theme="dark"] .login-box{background:#1B2741;border-color:#34435D;box-shadow:0 25px 70px rgba(0,0,0,.35)}
html[data-theme="dark"] input{background:#202E4B;color:#F7F4EF;border-color:#3B4B66}
html[data-theme="dark"] input:focus{border-color:#C9B59C;box-shadow:0 0 0 3px rgba(201,181,156,.12)}
html[data-theme="dark"] .language-switch,html[data-theme="dark"] .theme-switch{background:#202E4B;border-color:#3B4B66;color:#D7C5AB}
html[data-theme="dark"] .language-switch a{color:#BFC8D8}
html[data-theme="dark"] .language-switch a.active{background:linear-gradient(135deg,#C9B59C,#AD9272);color:#172039}
html[data-theme="dark"] button[type="submit"]{background:linear-gradient(135deg,#C9B59C,#AD9272);color:#172039}
html[data-theme="dark"] .error{background:#47252B;color:#FECACA}

@media(max-width:520px){
.login-box{width:100%;padding:34px 24px;border-radius:20px}
.logo-stage,.logo-mark{width:285px;height:180px}.logo-mark{border-radius:23px}
.logo-mark img{width:310px;transform:translate(-50%,-74px)}
.login-controls{top:12px;inset-inline-end:12px}
}

</style>
</head>

<body>

<div class="login-controls">
<div class="language-switch">
    <a href="{{ route('language.switch', 'ar') }}" class="{{ app()->getLocale() === 'ar' ? 'active' : '' }}">AR</a>
    <a href="{{ route('language.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
</div>
<button type="button" class="theme-switch" id="loginThemeToggle" aria-label="{{ __('messages.dark_mode') }}"></button>
</div>

<div class="login-box">

<div class="logo">
<div class="logo-stage" aria-hidden="true">
<div class="logo-mark">
<img class="logo-light" src="{{ asset('images/al-waleed-logo-transparent.png') }}" alt="">
<img class="logo-dark" src="{{ asset('images/al-waleed-logo-dark.png') }}" alt="">
</div>
</div>
</div>

@if($errors->any())
<div class="error">
{{ $errors->first() }}
</div>
@endif

<form method="POST" action="/login">

@csrf

<label>{{ __('البريد الإلكتروني') }}</label>

<input
type="email"
name="email"
required>

<label>{{ __('كلمة المرور') }}</label>

<input
type="password"
name="password"
required>

<button type="submit">
{{ __('تسجيل الدخول') }}
</button>

</form>

</div>

<script>
(function(){
    const toggle = document.getElementById('loginThemeToggle');

    function applyTheme(theme){
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('alwaleed-theme', theme);
        const dark = theme === 'dark';
        toggle.textContent = dark ? '☀' : '☾';
        toggle.setAttribute('aria-label', dark ? @json(__('messages.light_mode')) : @json(__('messages.dark_mode')));
        toggle.title = toggle.getAttribute('aria-label');
    }

    toggle.addEventListener('click', function(){
        applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });

    applyTheme(document.documentElement.getAttribute('data-theme') || 'light');
})();
</script>

</body>
</html>
