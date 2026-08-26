<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Al Waleed</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

<script>
(function () {
    const savedTheme = localStorage.getItem('alwaleed-theme');

    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
    }
})();
</script>

<style>
*{
    box-sizing:border-box;
}

:root{
    --bg:#F8F6F2;
    --surface:#FFFFFF;
    --surface-soft:#F8FAFC;
    --sidebar-start:#F4EFE8;
    --sidebar-end:#EDE5DC;
    --border:#E8E1D8;
    --border-strong:#DDD7D1;

    --text:#1F1F1F;
    --text-soft:#8A8178;
    --accent:#CDBA9E;
    --accent-dark:#BFA98A;

    --menu-section:#A68A64;

    --shadow:0 12px 30px rgba(0,0,0,.04);
    --shadow-soft:0 10px 30px rgba(0,0,0,.05);

    --danger:#DC2626;
    --success:#15803D;
}

html[data-theme="dark"]{
    --bg:#11182A;
    --surface:#18223A;
    --surface-soft:#202D49;
    --sidebar-start:#172440;
    --sidebar-end:#10182C;

    --border:#2B3A59;
    --border-strong:#394A6B;

    --text:#F5F6FA;
    --text-soft:#AAB4C8;

    --accent:#C9B59C;
    --accent-dark:#B49A7B;

    --menu-section:#D4C0A6;

    --shadow:0 16px 40px rgba(7,15,35,.24);
    --shadow-soft:0 12px 34px rgba(7,15,35,.20);

    --danger:#E56D76;
    --success:#62B98B;
}

html[data-theme="dark"] body{
    background:
        radial-gradient(circle at top right, rgba(83,105,160,.12), transparent 30%),
        radial-gradient(circle at bottom left, rgba(201,181,156,.045), transparent 25%),
        var(--bg);
}

html[data-theme="dark"] .sidebar{
    background:
        linear-gradient(180deg,#1A2949 0%,#15213B 55%,#10182C 100%);
    border-left-color:#2B3A59;
    box-shadow:-18px 0 45px rgba(7,15,35,.22);
}

html[data-theme="dark"] .brand,
html[data-theme="dark"] .theme-box,
html[data-theme="dark"] .top-nav,
html[data-theme="dark"] .card{
    background:linear-gradient(145deg,#1B2741,#172139);
    border-color:#2D3D5C;
}

html[data-theme="dark"] .brand{
    box-shadow:0 14px 34px rgba(7,15,35,.20);
}

html[data-theme="dark"] .user-box{
    background:linear-gradient(145deg,#202E4B,#1B2843);
    border-color:#30415F;
}

html[data-theme="dark"] .theme-icon{
    background:#22314F;
    border-color:#354766;
    color:#D9C6AC;
}

html[data-theme="dark"] .theme-toggle{
    background:#9D876C;
    box-shadow:inset 0 0 0 1px rgba(255,255,255,.06);
}

html[data-theme="dark"] .menu a:hover{
    background:#21304E;
    border-color:#354766;
    color:#D8C4A8;
}

html[data-theme="dark"] .menu a.active{
    background:linear-gradient(135deg,#263653,#21304B);
    color:#E2CFB5;
    border-color:#75634E;
    box-shadow:
        0 12px 28px rgba(7,15,35,.18),
        inset 3px 0 0 rgba(201,181,156,.48);
}

html[data-theme="dark"] input,
html[data-theme="dark"] textarea,
html[data-theme="dark"] select,
html[data-theme="dark"] .search-box{
    background:#202D49;
    border-color:#364867;
    color:#F5F6FA;
}

html[data-theme="dark"] input:focus,
html[data-theme="dark"] textarea:focus,
html[data-theme="dark"] select:focus,
html[data-theme="dark"] .search-box:focus{
    background:#243351;
    border-color:#A18A6D;
    box-shadow:0 0 0 3px rgba(201,181,156,.09);
}

html[data-theme="dark"] .avatar{
    background:linear-gradient(135deg,#C9B59C,#A98D6D);
    box-shadow:0 8px 18px rgba(7,15,35,.18);
}

html[data-theme="dark"] button,
html[data-theme="dark"] .btn{
    background:linear-gradient(135deg,#C9B59C,#AD9272);
    color:#172039;
}

html[data-theme="dark"] button:hover,
html[data-theme="dark"] .btn:hover{
    background:linear-gradient(135deg,#BDA78B,#9F8263);
}

html[data-theme="dark"] .danger{
    background:#B8525A;
    color:#FFF;
}

html[data-theme="dark"] .success{
    background:#4F9D70;
    color:#FFF;
}

html[data-theme="dark"] .logout-btn{
    background:#EEF0F6;
    color:#172039;
}

html[data-theme="dark"] table td{
    background:#202D49;
}

html[data-theme="dark"] table th{
    color:#AEB8CB;
}

html{
    background:var(--bg);
}

body{
    margin:0;
    font-family:Tajawal;
    background:var(--bg);
    color:var(--text);
    transition:
        background .25s ease,
        color .25s ease;
}

.app{
    display:flex;
    min-height:100vh;
}

.sidebar{
    width:292px;
    height:100vh;
    position:fixed;
    right:0;
    top:0;

    background:linear-gradient(
        180deg,
        var(--sidebar-start),
        var(--sidebar-end)
    );

    padding:22px;
    border-left:1px solid var(--border);
    box-shadow:-12px 0 35px rgba(0,0,0,.05);
    overflow-y:auto;
    z-index:3000;

    transition:
        background .25s ease,
        border-color .25s ease;
}

.brand{
    background:var(--surface);
    border:1px solid var(--border);

    padding:18px;
    border-radius:24px;
    text-align:center;
    margin-bottom:22px;

    box-shadow:var(--shadow);

    transition:
        background .25s ease,
        border-color .25s ease;
}

.brand img{
    width:185px;
    height:auto;
    display:block;
    margin:0 auto 10px auto;
}

.brand p{
    margin:0;
    color:var(--text-soft);
    font-size:13px;
    font-weight:700;
}

.user-box{
    background:var(--surface-soft);
    border:1px solid var(--border);

    border-radius:20px;
    padding:15px;
    margin-bottom:15px;

    transition:
        background .25s ease,
        border-color .25s ease;
}

.user-box strong{
    display:block;
    font-size:15px;
    margin-bottom:5px;
}

.user-box span{
    font-size:12px;
    color:var(--text-soft);
}

.theme-box{
    display:flex;
    align-items:center;
    justify-content:space-between;

    background:var(--surface);
    border:1px solid var(--border);

    border-radius:18px;
    padding:11px 13px;
    margin-bottom:22px;

    box-shadow:var(--shadow);

    transition:
        background .25s ease,
        border-color .25s ease;
}

.theme-info{
    display:flex;
    align-items:center;
    gap:9px;
}

.theme-icon{
    width:34px;
    height:34px;

    border-radius:11px;

    display:flex;
    align-items:center;
    justify-content:center;

    background:var(--surface-soft);
    border:1px solid var(--border);

    font-size:15px;
}

.theme-text strong{
    display:block;
    font-size:12px;
}

.theme-text span{
    display:block;
    color:var(--text-soft);
    font-size:10px;
    margin-top:2px;
}

.theme-toggle{
    width:50px;
    height:28px;
    border:0;
    padding:3px;

    border-radius:999px;
    background:#D6D0C8;

    position:relative;
    cursor:pointer;

    transition:.25s;
}

.theme-toggle::before{
    content:"";

    position:absolute;
    top:4px;
    right:4px;

    width:20px;
    height:20px;

    background:#FFFFFF;
    border-radius:50%;

    box-shadow:0 3px 8px rgba(0,0,0,.18);

    transition:.25s;
}

html[data-theme="dark"] .theme-toggle{
    background:var(--accent);
}

html[data-theme="dark"] .theme-toggle::before{
    transform:translateX(-22px);
}

.menu-section{
    margin:18px 0 8px;

    color:var(--menu-section);

    font-size:12px;
    font-weight:900;
    letter-spacing:.5px;
}

.menu a{
    display:flex;
    align-items:center;
    gap:10px;

    color:var(--text);
    text-decoration:none;

    padding:13px 15px;
    border-radius:15px;
    margin-bottom:7px;

    font-weight:700;

    transition:.25s;

    border:1px solid transparent;
}

.menu a:hover{
    background:var(--surface);
    border-color:var(--border);
    color:var(--accent);

    transform:translateX(-4px);
}

.menu a.active{
    background:var(--surface);
    color:var(--accent);

    border-color:var(--accent);

    box-shadow:var(--shadow);
}

.logout-btn{
    width:100%;
    margin-top:18px;

    background:var(--text);
    color:var(--bg);

    border:0;

    padding:13px;
    border-radius:16px;

    font-family:Tajawal;
    font-weight:800;
    cursor:pointer;
}

html[data-theme="dark"] .logout-btn{
    background:#F3F4F6;
    color:#151719;
}

.content{
    margin-right:292px;
    width:calc(100% - 292px);
    padding:28px;
}

.top-nav{
    min-height:74px;

    position:relative;
    z-index:2000;
    overflow:visible;

    background:var(--surface);
    border:1px solid var(--border);

    border-radius:24px;

    box-shadow:var(--shadow);

    margin-bottom:24px;

    padding:12px 22px;

    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:15px;

    transition:
        background .25s ease,
        border-color .25s ease;
}

.search-box{
    width:360px;

    background:var(--surface-soft);
    color:var(--text);

    border:1px solid var(--border);

    border-radius:16px;

    padding:13px 16px;

    font-family:Tajawal;
}

.search-box::placeholder{
    color:var(--text-soft);
}

.nav-user{
    display:flex;
    align-items:center;
    gap:12px;
}

.avatar{
    width:42px;
    height:42px;

    border-radius:14px;

    background:var(--accent);
    color:white;

    display:flex;
    align-items:center;
    justify-content:center;

    font-weight:900;
}

.page-title{
    margin:0;

    font-size:30px;
    font-weight:900;

    color:var(--text);
}

.card{
    background:var(--surface);

    border-radius:24px;

    padding:24px;

    box-shadow:var(--shadow-soft);

    margin-bottom:22px;

    border:1px solid var(--border);

    transition:
        background .25s ease,
        border-color .25s ease;
}

input,
textarea,
select{
    width:100%;

    padding:14px 16px;

    border:1px solid var(--border-strong);
    border-radius:14px;

    background:var(--surface-soft);
    color:var(--text);

    font-family:Tajawal;
    font-size:15px;
}

input::placeholder,
textarea::placeholder{
    color:var(--text-soft);
}

input:focus,
textarea:focus,
select:focus{
    outline:none;

    border-color:var(--accent);

    background:var(--surface);
}

option{
    background:var(--surface);
    color:var(--text);
}

button,
.btn{
    background:var(--accent);
    color:white;

    border:0;

    padding:13px 22px;
    border-radius:14px;

    font-weight:700;
    cursor:pointer;

    text-decoration:none;
    display:inline-block;

    font-family:Tajawal;
}

button:hover,
.btn:hover{
    background:var(--accent-dark);
}

.danger{
    background:var(--danger);
}

.success{
    background:var(--success);
}

table{
    width:100%;

    border-collapse:separate;
    border-spacing:0 10px;
}

th{
    color:var(--text-soft);
    text-align:right;
    padding:10px;
}

td{
    background:var(--surface-soft);
    padding:14px;

    transition:background .25s ease;
}

td:first-child{
    border-radius:0 14px 14px 0;
}

td:last-child{
    border-radius:14px 0 0 14px;
}

/*
|--------------------------------------------------------------------------
| Dark mode override للعناصر اللي داخل الصفحات
|--------------------------------------------------------------------------
*/

html[data-theme="dark"] .topbar,
html[data-theme="dark"] .dashboard-head,
html[data-theme="dark"] .dashboard-card,
html[data-theme="dark"] .stat-box,
html[data-theme="dark"] .mini-stat,
html[data-theme="dark"] .owner-hero,
html[data-theme="dark"] .owner-stat,
html[data-theme="dark"] .owner-card,
html[data-theme="dark"] .panel,
html[data-theme="dark"] .info-card,
html[data-theme="dark"] .summary-box,
html[data-theme="dark"] .movement-item,
html[data-theme="dark"] .quick-action,
html[data-theme="dark"] .quick-link,
html[data-theme="dark"] .quick-owner a,
html[data-theme="dark"] .company-box{
    background:var(--surface) !important;
    border-color:var(--border) !important;
    color:var(--text) !important;
}

html[data-theme="dark"] .movement-item,
html[data-theme="dark"] .quick-action,
html[data-theme="dark"] .quick-link,
html[data-theme="dark"] .quick-owner a,
html[data-theme="dark"] .company-box{
    background:var(--surface-soft) !important;
}

html[data-theme="dark"] p,
html[data-theme="dark"] .stat-title,
html[data-theme="dark"] .stat-small,
html[data-theme="dark"] .stat-note{
    color:var(--text-soft);
}

html[data-theme="dark"] h1,
html[data-theme="dark"] h2,
html[data-theme="dark"] h3,
html[data-theme="dark"] strong{
    color:inherit;
}

html[data-theme="dark"] .company-table td,
html[data-theme="dark"] .owner-table td,
html[data-theme="dark"] .simple-table td{
    background:transparent !important;
    border-color:var(--border) !important;
}

html[data-theme="dark"] .company-table th,
html[data-theme="dark"] .owner-table th,
html[data-theme="dark"] .simple-table th{
    border-color:var(--border) !important;
}

html[data-theme="dark"] .company-code{
    background:var(--surface-soft) !important;
    color:var(--text-soft) !important;
}

html[data-theme="dark"] .alert-item,
html[data-theme="dark"] .expiry-item{
    background:linear-gradient(135deg,#29344A,#242E43) !important;
    border-color:#5A5361 !important;
}

html[data-theme="dark"] .alert-days,
html[data-theme="dark"] .expiry-days{
    background:#202A40 !important;
    border-color:#665B67 !important;
    color:#E0C9A8 !important;
}

.sidebar-toggle,
.sidebar-close{
    display:none;
}

.sidebar-overlay{
    display:none;
}

@media(max-width:900px){
    body.sidebar-open{
        overflow:hidden;
    }

    .sidebar{
        width:min(86vw, 320px);
        height:100dvh;
        right:0;
        left:auto;
        transform:translateX(105%);
        transition:transform .3s ease, background .25s ease, border-color .25s ease;
    }

    html[dir="ltr"] .sidebar{
        left:0;
        right:auto;
        transform:translateX(-105%);
    }

    body.sidebar-open .sidebar{
        transform:translateX(0);
    }

    .sidebar-overlay{
        display:block;
        position:fixed;
        inset:0;
        z-index:2900;
        border:0;
        border-radius:0;
        padding:0;
        background:rgba(15,23,42,.45);
        opacity:0;
        visibility:hidden;
        pointer-events:none;
        transition:opacity .25s ease, visibility .25s ease;
        backdrop-filter:blur(2px);
    }

    html[data-theme="dark"] .sidebar-overlay,
    html[data-theme="dark"] .sidebar-overlay:hover{
        background:rgba(3,7,18,.62);
    }

    body.sidebar-open .sidebar-overlay{
        opacity:1;
        visibility:visible;
        pointer-events:auto;
    }

    .sidebar-close{
        display:flex;
        position:absolute;
        top:12px;
        inset-inline-end:12px;
        width:38px;
        height:38px;
        padding:0;
        align-items:center;
        justify-content:center;
        border-radius:12px;
        z-index:2;
        font-size:22px;
    }

    .sidebar-toggle{
        display:flex;
        width:42px;
        height:42px;
        padding:0;
        align-items:center;
        justify-content:center;
        flex:0 0 42px;
    }

    .content,
    html[dir="ltr"] .content{
        margin:0;
        width:100%;
        padding:16px;
    }

    .app{
        display:block;
    }

    .search-wrap{
        display:none;
    }
}

/* ==========================================================================
   Bilingual + polished UI
   ========================================================================== */

html[dir="ltr"] .sidebar{
    right:auto;
    left:0;
    border-left:0;
    border-right:1px solid var(--border);
    box-shadow:12px 0 35px rgba(0,0,0,.05);
}

html[dir="ltr"] .content{
    margin-right:0;
    margin-left:292px;
}

html[dir="ltr"] th{
    text-align:left;
}

html[dir="ltr"] td:first-child{
    border-radius:14px 0 0 14px;
}

html[dir="ltr"] td:last-child{
    border-radius:0 14px 14px 0;
}

html[dir="ltr"] .menu a:hover{
    transform:translateX(4px);
}

.icon{
    width:19px;
    height:19px;
    flex:0 0 19px;
    stroke:currentColor;
    stroke-width:1.9;
    fill:none;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.menu a .icon{
    color:var(--text-soft);
    transition:.2s;
}

.menu a:hover .icon,
.menu a.active .icon{
    color:var(--accent);
}

.menu a{
    position:relative;
    overflow:hidden;
}

.menu a.active::after{
    content:"";
    position:absolute;
    top:9px;
    bottom:9px;
    width:3px;
    border-radius:999px;
    background:var(--accent);
    right:8px;
}

html[dir="ltr"] .menu a.active::after{
    right:auto;
    left:8px;
}

.top-nav{
    backdrop-filter:blur(12px);
}

.top-nav-center{
    color:var(--text-soft);
    font-weight:800;
    font-size:13px;
    letter-spacing:.2px;
    white-space:nowrap;
}

.top-nav-actions{
    display:flex;
    align-items:center;
    gap:10px;
    position:relative;
    z-index:2100;
}

.language-switch{
    display:flex;
    align-items:center;
    gap:4px;
    padding:4px;
    background:var(--surface-soft);
    border:1px solid var(--border);
    border-radius:13px;
}

.language-switch a{
    min-width:38px;
    text-align:center;
    padding:7px 9px;
    border-radius:9px;
    text-decoration:none;
    color:var(--text-soft);
    font-size:12px;
    font-weight:900;
    transition:.2s;
}

.language-switch a:hover{
    color:var(--accent);
}

.language-switch a.active{
    background:var(--accent);
    color:#fff;
    box-shadow:0 5px 14px rgba(0,0,0,.08);
}

html[data-theme="dark"] .language-switch{
    background:#202D49;
    border-color:#364867;
}

html[data-theme="dark"] .language-switch a.active{
    background:linear-gradient(135deg,#C9B59C,#AD9272);
    color:#172039;
}

.nav-user{
    padding:6px 8px 6px 6px;
    border:1px solid transparent;
    border-radius:15px;
    transition:.2s;
}

.nav-user:hover{
    background:var(--surface-soft);
    border-color:var(--border);
}

.search-wrap{
    position:relative;
    width:360px;
}

.search-wrap .search-box{
    width:100%;
    padding-inline-start:42px;
}

.search-wrap .search-icon{
    position:absolute;
    inset-inline-start:14px;
    top:50%;
    transform:translateY(-50%);
    color:var(--text-soft);
    width:18px;
    height:18px;
    pointer-events:none;
}

.notification-center{position:relative;z-index:2200}.notification-bell{width:42px;height:42px;padding:0;border-radius:13px;display:grid;place-items:center;background:var(--surface-soft);color:var(--text);border:1px solid var(--border);position:relative}.notification-bell .icon{width:20px;height:20px}.notification-count{display:none;position:absolute;top:-6px;inset-inline-end:-6px;min-width:19px;height:19px;padding:0 5px;border-radius:999px;background:#dc2626;color:#fff;font:800 10px/19px Tajawal;text-align:center}.notification-count.show{display:block}.notification-panel{display:none;position:absolute;top:calc(100% + 10px);inset-inline-end:0;width:min(390px,90vw);background:var(--surface);border:1px solid var(--border);border-radius:19px;box-shadow:0 22px 55px rgba(15,23,42,.22);z-index:2300;overflow:hidden;isolation:isolate}.notification-panel.open{display:block}.notification-head{display:flex;align-items:center;justify-content:space-between;padding:14px 15px;border-bottom:1px solid var(--border)}.notification-head strong{font-size:14px}.notification-head button{background:transparent!important;color:var(--accent)!important;padding:4px;font-size:11px}.desktop-notification-enable{display:none;width:calc(100% - 16px);margin:8px;padding:9px;font-size:11px}.desktop-notification-enable.show{display:block}.notification-list{max-height:390px;overflow:auto;padding:7px}.notification-item{display:block;padding:11px;border-radius:12px;text-decoration:none;color:var(--text);border-bottom:1px solid var(--border);position:relative}.notification-item:last-child{border-bottom:0}.notification-item.unread{background:var(--surface-soft)}.notification-item.unread:before{content:"";position:absolute;top:15px;inset-inline-start:5px;width:6px;height:6px;border-radius:50%;background:var(--accent)}.notification-item strong{display:block;font-size:12px;padding-inline-start:5px}.notification-item p{margin:4px 0;color:var(--text-soft);font-size:11px;line-height:1.5;padding-inline-start:5px}.notification-item time{font-size:9px;color:var(--text-soft);padding-inline-start:5px}.notification-empty{text-align:center;color:var(--text-soft);padding:30px 15px;font-size:12px}

.search-suggestions{
    display:none;
    position:absolute;
    top:calc(100% + 9px);
    inset-inline-start:0;
    width:min(460px,80vw);
    z-index:1200;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:17px;
    padding:7px;
    box-shadow:0 20px 50px rgba(15,23,42,.16);
}

.search-suggestions.open{display:block}
.search-suggestion{display:flex;justify-content:space-between;gap:12px;padding:10px 11px;border-radius:11px;text-decoration:none;color:var(--text)}
.search-suggestion:hover{background:var(--surface-soft)}
.search-suggestion strong{display:block;font-size:13px}
.search-suggestion small{display:block;color:var(--text-soft);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px}
.search-suggestion em{font-style:normal;color:var(--accent);font-size:10px;font-weight:900;white-space:nowrap}

.brand{
    position:relative;
    overflow:hidden;
}

.brand::after{
    content:"";
    position:absolute;
    width:100px;
    height:100px;
    border-radius:50%;
    background:rgba(205,186,158,.12);
    inset-inline-end:-42px;
    top:-42px;
}

.user-box{
    position:relative;
}

.role-dot{
    width:7px;
    height:7px;
    display:inline-block;
    border-radius:50%;
    background:var(--success);
    margin-inline-end:6px;
    box-shadow:0 0 0 3px rgba(21,128,61,.08);
}

.settings-row{
    display:grid;
    grid-template-columns:1fr auto;
    gap:10px;
    align-items:center;
}

.theme-box{
    margin-bottom:10px;
}

.language-box{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:18px;
    padding:11px 13px;
    margin-bottom:22px;
    box-shadow:var(--shadow);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}

.language-box .label{
    display:flex;
    align-items:center;
    gap:9px;
    min-width:0;
}

.language-box .label strong{
    display:block;
    font-size:12px;
}

.language-box .label span{
    display:block;
    font-size:10px;
    color:var(--text-soft);
    margin-top:2px;
}

.language-box .language-switch{
    flex:0 0 auto;
}

html[data-theme="dark"] .language-box{
    background:linear-gradient(145deg,#1B2741,#172139);
    border-color:#2D3D5C;
}

.menu-section{
    display:flex;
    align-items:center;
    gap:8px;
}

.menu-section::after{
    content:"";
    height:1px;
    flex:1;
    background:linear-gradient(90deg,var(--border),transparent);
}

html[dir="ltr"] .menu-section::after{
    background:linear-gradient(270deg,var(--border),transparent);
}

.logout-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
}

.logout-btn .icon{
    width:18px;
    height:18px;
}

/* Desktop auto-collapsing sidebar: icons remain visible until mouse hover. */
@media(min-width:901px){
    .sidebar{
        width:84px;
        padding:16px 12px;
        overflow-x:hidden;
        transition:
            width .3s ease,
            padding .3s ease,
            background .25s ease,
            border-color .25s ease;
    }

    .sidebar:hover,
    .sidebar:focus-within{
        width:292px;
        padding:22px;
    }

    .content{
        margin-right:84px;
        width:calc(100% - 84px);
        transition:margin .3s ease, width .3s ease;
    }

    html[dir="ltr"] .content{
        margin-right:0;
        margin-left:84px;
    }

    .brand{
        padding:12px 8px;
        min-height:64px;
        display:flex;
        align-items:center;
        justify-content:center;
        overflow:hidden;
        transition:padding .3s ease, min-height .3s ease;
    }

    .brand img{
        width:48px;
        max-width:none;
        margin:0;
        transition:width .3s ease, margin .3s ease;
    }

    .brand p,
    .user-box,
    .theme-box,
    .language-box{
        opacity:0;
        visibility:hidden;
        max-height:0;
        padding-top:0;
        padding-bottom:0;
        margin-bottom:0;
        border-width:0;
        overflow:hidden;
        transition:opacity .15s ease, max-height .3s ease, margin .3s ease;
    }

    .menu-section{
        height:16px;
        margin:10px 0 5px;
        font-size:0;
        white-space:nowrap;
    }

    .menu-section::after{
        background:var(--border);
    }

    .menu a{
        justify-content:center;
        gap:0;
        height:50px;
        padding:13px;
        font-size:0;
        white-space:nowrap;
    }

    .menu a .icon{
        width:22px;
        height:22px;
        flex-basis:22px;
    }

    .menu a:hover{
        transform:none;
    }

    .logout-btn{
        height:48px;
        padding:12px;
        font-size:0;
        gap:0;
    }

    .sidebar:hover .brand,
    .sidebar:focus-within .brand{
        padding:18px;
        min-height:0;
        display:block;
    }

    .sidebar:hover .brand img,
    .sidebar:focus-within .brand img{
        width:185px;
        margin:0 auto 10px;
    }

    .sidebar:hover .brand p,
    .sidebar:focus-within .brand p,
    .sidebar:hover .user-box,
    .sidebar:focus-within .user-box,
    .sidebar:hover .theme-box,
    .sidebar:focus-within .theme-box,
    .sidebar:hover .language-box,
    .sidebar:focus-within .language-box{
        opacity:1;
        visibility:visible;
        max-height:160px;
        border-width:1px;
    }

    .sidebar:hover .user-box,
    .sidebar:focus-within .user-box{
        padding:15px;
        margin-bottom:15px;
    }

    .sidebar:hover .theme-box,
    .sidebar:focus-within .theme-box{
        padding:11px 13px;
        margin-bottom:10px;
    }

    .sidebar:hover .language-box,
    .sidebar:focus-within .language-box{
        padding:11px 13px;
        margin-bottom:22px;
    }

    .sidebar:hover .menu-section,
    .sidebar:focus-within .menu-section{
        height:auto;
        margin:18px 0 8px;
        font-size:12px;
    }

    .sidebar:hover .menu-section::after,
    .sidebar:focus-within .menu-section::after{
        background:linear-gradient(90deg,var(--border),transparent);
    }

    html[dir="ltr"] .sidebar:hover .menu-section::after,
    html[dir="ltr"] .sidebar:focus-within .menu-section::after{
        background:linear-gradient(270deg,var(--border),transparent);
    }

    .sidebar:hover .menu a,
    .sidebar:focus-within .menu a{
        justify-content:flex-start;
        gap:10px;
        height:auto;
        padding:13px 15px;
        font-size:initial;
    }

    .sidebar:hover .menu a:hover,
    .sidebar:focus-within .menu a:hover{
        transform:translateX(-4px);
    }

    html[dir="ltr"] .sidebar:hover .menu a:hover,
    html[dir="ltr"] .sidebar:focus-within .menu a:hover{
        transform:translateX(4px);
    }

    .sidebar:hover .logout-btn,
    .sidebar:focus-within .logout-btn{
        height:auto;
        padding:13px;
        font-size:initial;
        gap:8px;
    }
}

@media(max-width:900px){
    html[dir="ltr"] .content{
        margin-left:0;
    }

    .top-nav-actions{
        width:100%;
        justify-content:space-between;
    }

    .top-nav{
        flex-wrap:wrap;
    }
}

/* Shared layout safety: keep cards and grid children from overlapping. */
.content{
    min-width:0;
    overflow-x:clip;
}

.content > *,
.content :where(.card,.dashboard-card,.owner-card,.stat-box,.owner-stat,.mini-stat,.panel,.info-card,.summary-box,.company-box,.statement-panel,.statement-kpi,.statement-table-card,.ext-card,.result-card,.search-group){
    min-width:0;
    max-width:100%;
}

.content :where(.stats,.dashboard-grid,.owner-stats,.owner-grid,.statement-kpis,.statement-details,.detail-grid,.ext-grid,[style*="display:grid"],[style*="display: grid"]) > *{
    min-width:0;
    max-width:100%;
}

.content :where(.card,.dashboard-card,.owner-card,.panel,.info-card,.summary-box,.company-box,.statement-panel,.statement-kpi,.ext-card) :where(h1,h2,h3,p,strong,span,a,label){
    overflow-wrap:anywhere;
}

.content :where(.card,.dashboard-card,.owner-card,.panel,.statement-panel,.statement-table-card,.ext-card) :where(img,svg,canvas){
    max-width:100%;
}

.content :where(.card,.dashboard-card,.owner-card,.panel,.statement-panel,.statement-table-card,.ext-card) > :where([style*="overflow:auto"],[style*="overflow: auto"],.table-wrap,.statement-table-wrap){
    max-width:100%;
    overflow-x:auto !important;
    -webkit-overflow-scrolling:touch;
}

@media(max-width:1150px) and (min-width:701px){
    .content [style*="grid-template-columns:repeat(5"],
    .content [style*="grid-template-columns: repeat(5"],
    .content [style*="grid-template-columns:repeat(4"],
    .content [style*="grid-template-columns: repeat(4"],
    .content [style*="grid-template-columns:repeat(3"],
    .content [style*="grid-template-columns: repeat(3"]{
        grid-template-columns:repeat(2,minmax(0,1fr)) !important;
    }
}

@media(max-width:700px){
    .content{
        padding:14px;
    }

    .content [style*="grid-template-columns"],
    .content :where(.stats,.dashboard-grid,.owner-stats,.owner-grid,.statement-kpis,.statement-details,.detail-grid,.ext-grid){
        grid-template-columns:minmax(0,1fr) !important;
    }

    .content :where(.card,.dashboard-card,.owner-card,.panel,.info-card,.summary-box,.company-box,.statement-panel,.statement-kpi,.statement-table-card,.ext-card){
        padding:16px;
        border-radius:18px;
    }
}

</style>
</head>

<body>

<div class="app">

<aside class="sidebar" id="appSidebar">

<button type="button" class="sidebar-close" id="sidebarClose" aria-label="{{ __('messages.close_menu') }}">×</button>

<div class="brand">
    <img src="/logo.png" alt="Al Waleed">
    <p>{{ __('messages.brand_tagline') }}</p>
</div>


<div class="user-box">

    <strong>
        {{ auth()->user()->name ?? __('messages.user') }}
    </strong>

    <span>

        @if(auth()->user()->role == 'super_admin')

            {{ __('messages.system_owner') }}

        @elseif(auth()->user()->role == 'admin')

            {{ __('messages.company_manager') }}

        @elseif(auth()->user()->role == 'accountant')

            {{ __('messages.accountant') }}

        @elseif(auth()->user()->role == 'data_entry')

            {{ __('messages.data_entry') }}

        @else

            {{ __('messages.viewer') }}

        @endif

    </span>

</div>


<div class="theme-box">

    <div class="theme-info">

        <div class="theme-icon" id="themeIcon">
            ☀
        </div>

        <div class="theme-text">

            <strong id="themeTitle">
                {{ __('messages.light_mode') }}
            </strong>

            <span>
                {{ __('messages.appearance') }}
            </span>

        </div>

    </div>

    <button
        type="button"
        class="theme-toggle"
        id="themeToggle"
        aria-label="{{ __('messages.appearance') }}"
    ></button>

</div>

<div class="language-box">
    <div class="label">
        <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>
        <div>
            <strong>{{ __('messages.language') }}</strong>
            <span>{{ app()->getLocale() === 'ar' ? __('messages.arabic') : __('messages.english') }}</span>
        </div>
    </div>

    <div class="language-switch">
        <a href="{{ route('language.switch', 'ar') }}"
           class="{{ app()->getLocale() === 'ar' ? 'active' : '' }}">AR</a>
        <a href="{{ route('language.switch', 'en') }}"
           class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
    </div>
</div>


<nav class="menu">

    @if(auth()->user()->role == 'super_admin')

        <div class="menu-section">
            {{ __('messages.owner_dashboard') }}
        </div>

        <a
            href="/admin/dashboard"
            class="{{ request()->is('admin/dashboard') ? 'active' : '' }}"
        >
            <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg> {{ __('messages.owner_dashboard') }}
        </a>


        <div class="menu-section">
            {{ __('messages.companies_subscriptions') }}
        </div>

        <a
            href="/admin/companies"
            class="{{ request()->is('admin/companies') || request()->is('admin/companies/*') ? 'active' : '' }}"
        >
            <svg class="icon" viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"/></svg> {{ __('messages.companies') }}
        </a>

        <a
            href="/admin/companies/create"
            class="{{ request()->is('admin/companies/create') ? 'active' : '' }}"
        >
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg> {{ __('messages.add_company') }}
        </a>


        <div class="menu-section">
            {{ __('messages.platform_management') }}
        </div>

        <a
            href="/users"
            class="{{ request()->is('users*') ? 'active' : '' }}"
        >
            <svg class="icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> {{ __('messages.users') }}
        </a>

        <a
            href="/activity-logs"
            class="{{ request()->is('activity-logs*') ? 'active' : '' }}"
        >
            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> {{ __('messages.activity_logs') }}
        </a>

        <a
            href="/settings"
            class="{{ request()->is('settings*') ? 'active' : '' }}"
        >
            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.09A1.7 1.7 0 0 0 8 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 3.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H2v-4h.09A1.7 1.7 0 0 0 3.6 8a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 8 3.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V2h4v.09A1.7 1.7 0 0 0 15 3.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 8c.14.36.36.68.6 1 .27.3.65.47 1.1.4H21v4h-.09A1.7 1.7 0 0 0 19.4 15Z"/></svg> {{ __('messages.settings') }}
        </a>

        <a
            href="/backup"
            class="{{ request()->is('backup') ? 'active' : '' }}"
        >
            <svg class="icon" viewBox="0 0 24 24"><path d="M4 7h16v13H4z"/><path d="M7 4h10l3 3H4z"/><path d="M9 12h6M9 16h4"/></svg> {{ __('messages.backup') }}
        </a>

    @else

        <div class="menu-section">
            {{ __('messages.main') }}
        </div>

        <a
            href="/dashboard"
            class="{{ request()->is('dashboard') ? 'active' : '' }}"
        >
            <svg class="icon" viewBox="0 0 24 24"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg> {{ __('messages.dashboard') }}
        </a>


        @if(
            auth()->user()->role == 'admin' ||
            auth()->user()->role == 'accountant'
        )

            <div class="menu-section">
                {{ __('messages.finance') }}
            </div>

            <a
                href="/cashbox"
                class="{{ request()->is('cashbox') ? 'active' : '' }}"
            >
                <svg class="icon" viewBox="0 0 24 24"><path d="M4 6h14a2 2 0 0 1 2 2v10H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h12"/><path d="M16 11h4"/></svg> {{ __('messages.cashbox') }}
            </a>

            <a
                href="/receipts"
                class="{{ request()->is('receipts*') ? 'active' : '' }}"
            >
                <svg class="icon" viewBox="0 0 24 24"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg> {{ __('messages.receipts') }}
            </a>

            <a
                href="/payments"
                class="{{ request()->is('payments*') ? 'active' : '' }}"
            >
                <svg class="icon" viewBox="0 0 24 24"><path d="M12 21V9"/><path d="m7 14 5-5 5 5"/><path d="M5 3h14"/></svg> {{ __('messages.payments') }}
            </a>

            <a
                href="/reports"
                class="{{ request()->is('reports*') ? 'active' : '' }}"
            >
                <svg class="icon" viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/></svg> {{ __('messages.reports') }}
            </a>

            @php($activeCompany = auth()->user()->company)
            @if($activeCompany)
                @foreach(['inventory','sales','purchases','payroll','projects','installments'] as $featureKey)
                    @if($activeCompany->hasFeature($featureKey))
                        <a href="/modules/{{ $featureKey }}" class="{{ request()->is('modules/'.$featureKey.'*') ? 'active' : '' }}">
                            <span style="font-size:18px">{{ config('features.modules.'.$featureKey.'.icon') }}</span>
                            {{ __(config('features.modules.'.$featureKey.'.name')) }}
                        </a>
                    @endif
                @endforeach
                @if($activeCompany->hasFeature('voucher_attachments'))
                    <a href="/voucher-attachments" class="{{ request()->is('voucher-attachments*') ? 'active' : '' }}">
                        <span style="font-size:18px">{{ config('features.modules.voucher_attachments.icon') }}</span>
                        {{ __(config('features.modules.voucher_attachments.name')) }}
                    </a>
                @endif
            @endif

        @endif


        @if(
            auth()->user()->role == 'admin' ||
            auth()->user()->role == 'data_entry'
        )

            <div class="menu-section">
                {{ __('messages.clients') }}
            </div>

            <a
                href="/customers"
                class="{{ request()->is('customers*') ? 'active' : '' }}"
            >
                <svg class="icon" viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M2 20a6 6 0 0 1 12 0"/><path d="M14 20a4.5 4.5 0 0 1 8 0"/></svg> {{ __('messages.customers') }}
            </a>

            <a
                href="/suppliers"
                class="{{ request()->is('suppliers*') ? 'active' : '' }}"
            >
                <svg class="icon" viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"/></svg> {{ __('messages.suppliers') }}
            </a>

        @endif


        @if(auth()->user()->role == 'admin')

            <div class="menu-section">
                {{ __('messages.company_management') }}
            </div>

            <a
                href="/users"
                class="{{ request()->is('users*') ? 'active' : '' }}"
            >
                <svg class="icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> {{ __('messages.users') }}
            </a>

            <a
                href="/activity-logs"
                class="{{ request()->is('activity-logs*') ? 'active' : '' }}"
            >
                <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> {{ __('messages.activity_logs') }}
            </a>

            <a
                href="/settings"
                class="{{ request()->is('settings*') ? 'active' : '' }}"
            >
                <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.09A1.7 1.7 0 0 0 8 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 3.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H2v-4h.09A1.7 1.7 0 0 0 3.6 8a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 8 3.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V2h4v.09A1.7 1.7 0 0 0 15 3.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 8c.14.36.36.68.6 1 .27.3.65.47 1.1.4H21v4h-.09A1.7 1.7 0 0 0 19.4 15Z"/></svg> {{ __('messages.settings') }}
            </a>

        @endif

    @endif


    <a href="{{ route('search.index') }}" class="{{ request()->is('search') ? 'active' : '' }}">
        <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        {{ __('messages.global_search') }}
    </a>


    <form method="POST" action="/logout">

        @csrf

        <button class="logout-btn">
            <svg class="icon" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 3v18h-7"/></svg>
            {{ __('messages.logout') }}
        </button>

    </form>

</nav>

</aside>

<button type="button" class="sidebar-overlay" id="sidebarOverlay" aria-label="{{ __('messages.close_menu') }}"></button>


<main class="content">

    <div class="top-nav">

        <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="{{ __('messages.open_menu') }}" aria-controls="appSidebar" aria-expanded="false">
            <svg class="icon" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <form class="search-wrap" action="{{ route('search.index') }}" method="GET" id="globalSearchForm">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input
                class="search-box"
                type="text"
                name="q"
                id="globalSearchInput"
                value="{{ request('q') }}"
                autocomplete="off"
                placeholder="{{ __('messages.search') }}"
            >
            <div class="search-suggestions" id="searchSuggestions"></div>
        </form>

        <div class="top-nav-center">

            @if(auth()->user()->role == 'super_admin')

                {{ __('messages.platform_name') }}

            @else

                {{ __('messages.system_name') }}

            @endif

        </div>

        <div class="top-nav-actions">
            <div class="notification-center" id="notificationCenter">
                <button type="button" class="notification-bell" id="notificationBell" aria-label="{{ __('messages.notifications') }}">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                    <span class="notification-count" id="notificationCount"></span>
                </button>
                <div class="notification-panel" id="notificationPanel">
                    <div class="notification-head"><strong>{{ __('messages.notifications') }}</strong><button type="button" id="markNotificationsRead">{{ __('messages.mark_all_read') }}</button></div>
                    <button type="button" class="desktop-notification-enable" id="enableDesktopNotifications">{{ __('messages.enable_desktop_notifications') }}</button>
                    <div class="notification-list" id="notificationList"><div class="notification-empty">{{ __('messages.no_notifications') }}</div></div>
                </div>
            </div>
            <div class="language-switch">
                <a href="{{ route('language.switch', 'ar') }}"
                   class="{{ app()->getLocale() === 'ar' ? 'active' : '' }}">AR</a>
                <a href="{{ route('language.switch', 'en') }}"
                   class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
            </div>
        </div>

        <div class="nav-user">

            <div>

                <strong>
                    {{ auth()->user()->name ?? __('messages.user') }}
                </strong>

                <div style="font-size:12px;color:var(--text-soft)"><span class="role-dot"></span>{{ __('messages.online') }}</div>

            </div>

            <div class="avatar">
                {{ mb_substr(auth()->user()->name ?? 'U',0,1) }}
            </div>

        </div>

    </div>


    @yield('content')

</main>

</div>


<script>
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const themeTitle = document.getElementById('themeTitle');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarClose = document.getElementById('sidebarClose');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function setSidebar(open) {
    document.body.classList.toggle('sidebar-open', open);
    sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
}

sidebarToggle.addEventListener('click', () => setSidebar(true));
sidebarClose.addEventListener('click', () => setSidebar(false));
sidebarOverlay.addEventListener('click', () => setSidebar(false));

document.querySelectorAll('#appSidebar a').forEach((link) => {
    link.addEventListener('click', () => setSidebar(false));
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setSidebar(false);
    }
});

window.addEventListener('resize', () => {
    if (window.innerWidth > 900) {
        setSidebar(false);
    }
});

const globalSearchInput = document.getElementById('globalSearchInput');
const searchSuggestions = document.getElementById('searchSuggestions');
let searchTimer;
let searchRequest;

function closeSearchSuggestions() {
    searchSuggestions.classList.remove('open');
    searchSuggestions.innerHTML = '';
}

globalSearchInput.addEventListener('input', function () {
    clearTimeout(searchTimer);
    const term = this.value.trim();

    if (!term) {
        closeSearchSuggestions();
        return;
    }

    searchTimer = setTimeout(async () => {
        if (searchRequest) searchRequest.abort();
        searchRequest = new AbortController();

        try {
            const response = await fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(term)}`, {
                headers: {'Accept': 'application/json'},
                signal: searchRequest.signal
            });
            if (!response.ok) return closeSearchSuggestions();
            const items = await response.json();
            searchSuggestions.innerHTML = items.map(item => `
                <a class="search-suggestion" href="${item.url}">
                    <span><strong>${escapeSearchHtml(item.title)}</strong><small>${escapeSearchHtml(item.meta || '')}</small></span>
                    <em>${escapeSearchHtml(item.type)}</em>
                </a>`).join('');
            searchSuggestions.classList.toggle('open', items.length > 0);
        } catch (error) {
            if (error.name !== 'AbortError') closeSearchSuggestions();
        }
    }, 220);
});

function escapeSearchHtml(value) {
    const element = document.createElement('div');
    element.textContent = value;
    return element.innerHTML;
}

document.addEventListener('click', (event) => {
    if (!event.target.closest('#globalSearchForm')) closeSearchSuggestions();
});

const notificationBell = document.getElementById('notificationBell');
const notificationPanel = document.getElementById('notificationPanel');
const notificationCount = document.getElementById('notificationCount');
const notificationList = document.getElementById('notificationList');
const enableDesktopNotifications = document.getElementById('enableDesktopNotifications');
const markNotificationsRead = document.getElementById('markNotificationsRead');
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let notificationItems = [];

function notificationPermissionButton() {
    const available = 'Notification' in window;
    enableDesktopNotifications.classList.toggle('show', available && Notification.permission === 'default');
}

async function loadNotifications(showDesktop = true) {
    try {
        const response = await fetch('{{ route('notifications.index') }}', {headers:{'Accept':'application/json'}});
        if (!response.ok) return;
        const data = await response.json();
        notificationItems = data.notifications;
        notificationCount.textContent = data.unread > 99 ? '99+' : data.unread;
        notificationCount.classList.toggle('show', data.unread > 0);
        renderNotifications();
        if (showDesktop) showDesktopNotifications(notificationItems.filter(item => !item.read));
    } catch (_) {}
}

function renderNotifications() {
    if (!notificationItems.length) {
        notificationList.innerHTML = `<div class="notification-empty">{{ __('messages.no_notifications') }}</div>`;
        return;
    }
    notificationList.innerHTML = notificationItems.map(item => `
        <a href="${escapeSearchHtml(item.link || '#')}" class="notification-item ${item.read ? '' : 'unread'}" data-id="${item.id}">
            <strong>${escapeSearchHtml(item.title)}</strong>
            <p>${escapeSearchHtml(item.message)}</p>
            <time>${escapeSearchHtml(item.time)}</time>
        </a>`).join('');
}

function showDesktopNotifications(items) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    let seen = JSON.parse(localStorage.getItem('alwaleed-seen-notifications') || '[]');
    const unseen = items.filter(item => !seen.includes(item.id)).slice(0, 5);
    unseen.forEach(item => {
        const desktop = new Notification(item.title, {body:item.message, icon:'/logo.png', tag:`alwaleed-${item.id}`});
        desktop.onclick = () => { window.focus(); window.location.href = item.link || '/dashboard'; };
        seen.push(item.id);
    });
    localStorage.setItem('alwaleed-seen-notifications', JSON.stringify(seen.slice(-200)));
}

notificationBell.addEventListener('click', (event) => {
    event.stopPropagation();
    notificationPanel.classList.toggle('open');
    notificationPermissionButton();
});

enableDesktopNotifications.addEventListener('click', async () => {
    const permission = await Notification.requestPermission();
    notificationPermissionButton();
    if (permission === 'granted') showDesktopNotifications(notificationItems.filter(item => !item.read));
    if (permission === 'denied') alert(@json(__('messages.notification_permission_denied')));
});

notificationList.addEventListener('click', async (event) => {
    const item = event.target.closest('.notification-item');
    if (!item) return;
    event.preventDefault();
    await fetch(`/notifications/${item.dataset.id}/read`, {method:'POST',headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}});
    window.location.href = item.getAttribute('href');
});

markNotificationsRead.addEventListener('click', async () => {
    await fetch('{{ route('notifications.read-all') }}', {method:'POST',headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}});
    await loadNotifications(false);
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('#notificationCenter')) notificationPanel.classList.remove('open');
});

notificationPermissionButton();
loadNotifications();
setInterval(() => loadNotifications(true), {{ config('notifications.poll_seconds', 20) * 1000 }});

function applyTheme(theme) {

    document.documentElement.setAttribute(
        'data-theme',
        theme
    );

    localStorage.setItem(
        'alwaleed-theme',
        theme
    );

    if (theme === 'dark') {

        themeIcon.textContent = '☾';
        themeTitle.textContent = @json(__('messages.dark_mode'));

    } else {

        themeIcon.textContent = '☀';
        themeTitle.textContent = @json(__('messages.light_mode'));
    }
}

function currentTheme() {

    return document.documentElement
        .getAttribute('data-theme') || 'light';
}

themeToggle.addEventListener('click', function () {

    const nextTheme =
        currentTheme() === 'dark'
            ? 'light'
            : 'dark';

    applyTheme(nextTheme);
});

applyTheme(currentTheme());
</script>

</body>
</html>
