<div class="kudia-hero"><div><h1>مصنع كوديا</h1><p>الزبائن والفواتير وكشوفات الحساب</p></div><div class="kudia-wordmark" dir="ltr">KUDIA</div></div>
<nav class="kudia-nav" aria-label="مصنع كوديا">
    <a href="{{ route('kudia.index') }}" class="{{ request()->routeIs('kudia.index') ? 'active' : '' }}">الفواتير</a>
    <a href="{{ route('kudia.invoices.create') }}" class="{{ request()->routeIs('kudia.invoices.create') ? 'active' : '' }}">فاتورة جديدة</a>
    <a href="{{ route('kudia.customers.index') }}">الزبائن وكشوفات الحساب</a>
    @if(in_array(auth()->user()->role, ['admin', 'accountant']))<a href="/receipts">سندات القبض والتسديد</a>@endif
</nav>
