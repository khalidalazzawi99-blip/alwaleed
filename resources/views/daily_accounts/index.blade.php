@extends('layouts.app')
@section('content')
@include('daily_accounts._styles')
@php($money = fn($value, $code) => rtrim(rtrim(number_format((float) $value, 2, '.', ','), '0'), '.').' '.$code)
@php($dualMoney = fn($iqd, $usd) => '<span class="da-currency-line iqd">'.$money($iqd, 'IQD').'</span><span class="da-currency-line usd">'.$money($usd, 'USD').'</span>')
<div class="da">
<header class="da-head"><div><h1>الحسابات اليومية</h1><p>أضواء سيبار · متابعة المصروفات اليومية</p></div><span class="da-badge">السنة {{ $filters['year'] }} · IQD / USD</span></header>
@if(session('success'))<div class="da-alert" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="da-alert da-errors" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="da-actions">
@can('sippar.daily_accounts.create')<button type="button" class="da-primary" data-dialog="expense-dialog">+ إضافة مصروف</button>@endcan
@can('sippar.daily_accounts.export')
<a class="da-btn" href="{{ route('sippar.daily-accounts.excel', $filters, false) }}">تصدير Excel</a>
<a class="da-btn" href="{{ route('sippar.daily-accounts.pdf', $filters, false) }}">تصدير PDF</a>
@endcan
@can('sippar.daily_accounts.create')<button type="button" data-dialog="party-dialog">+ إضافة جهة / شخص</button>@endcan
</div>
<form class="da-panel" action="{{ route('sippar.daily-accounts.index', [], false) }}" method="get">
<h2>تصفية الحركات</h2>
<div class="da-grid">
<label>من تاريخ<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
<label>إلى تاريخ<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
<label>الشهر<select name="month"><option value="">كل الأشهر</option>@foreach($summary['months'] as $month)<option value="{{ $month['month'] }}" @selected(($filters['month'] ?? '') == $month['month'])>{{ $month['name'] }}</option>@endforeach</select></label>
<label>السنة<input type="number" name="year" min="1900" max="9998" list="daily-years" required value="{{ $filters['year'] }}"><datalist id="daily-years">@foreach(range(now()->year - 5, now()->year + 1) as $year)<option value="{{ $year }}">@endforeach</datalist></label>
<label>الشخص / الجهة<select name="party_id"><option value="">كل الأشخاص</option>@foreach($parties as $party)<option value="{{ $party->id }}" @selected(($filters['party_id'] ?? '') == $party->id)>{{ $party->name }}</option>@endforeach</select></label>
<label>بحث بالملاحظات<input name="notes" maxlength="200" placeholder="ابحث عن مصروف..." value="{{ $filters['notes'] ?? '' }}"></label>
<label>الحد الأدنى للمبلغ<input type="number" name="min_amount" min="0" step="0.01" value="{{ $filters['min_amount'] ?? '' }}"></label>
<label>الحد الأعلى للمبلغ<input type="number" name="max_amount" min="0" step="0.01" value="{{ $filters['max_amount'] ?? '' }}"></label>
</div>
<div class="da-actions"><button type="submit" class="da-primary">تطبيق</button><a class="da-btn" href="{{ route('sippar.daily-accounts.index', [], false) }}">إعادة تعيين</a></div>
<p>جميع المؤشرات أدناه تخص السنة والعملة والفلاتر المحددة.</p>
</form>
<div class="da-stats">
<div class="da-stat da-total-stat"><span>إجمالي المصروف</span><strong class="da-money da-dual-money">{!! $dualMoney($summary['total_iqd'], $summary['total_usd']) !!}</strong><small>الدينار أعلى والدولار أسفل</small></div>
<div class="da-stat"><span>أعلى شخص صرف</span><strong>{{ $summary['top_person']?->party?->name ?? '—' }}</strong><small class="da-money da-dual-money">{!! $dualMoney($summary['top_person']?->total_iqd ?? 0, $summary['top_person']?->total_usd ?? 0) !!}</small></div>
<div class="da-stat"><span>عدد الحركات</span><strong>{{ number_format($summary['count']) }}</strong><small>حركة مالية</small></div>
<div class="da-stat"><span>أعلى شهر صرف</span><strong>{{ $summary['top_month']['name'] ?? '—' }}</strong><small class="da-money da-dual-money">{!! $dualMoney($summary['top_month']['total_iqd'] ?? 0, $summary['top_month']['total_usd'] ?? 0) !!}</small></div>
<div class="da-stat"><span>متوسط المصروف</span><strong class="da-money da-dual-money">{!! $dualMoney($summary['average_iqd'], $summary['average_usd']) !!}</strong><small>لكل حركة</small></div>
<div class="da-stat"><span>آخر تاريخ حركة</span><strong>{{ $summary['last_date'] ?? '—' }}</strong><small>آخر مصروف ضمن النتائج</small></div>
</div>
<div class="da-two">
<section class="da-panel"><h2>المصروف حسب الشخص</h2><div class="da-subscroll"><table><thead><tr><th>الشخص</th><th>الإجمالي</th></tr></thead><tbody>
@forelse($summary['people'] as $person)<tr><td><a href="{{ route('sippar.daily-accounts.index', array_replace($filters, ['party_id' => $person->party_id]), false) }}">{{ $person->party->name }}</a></td><td class="da-money da-dual-money">{!! $dualMoney($person->total_iqd, $person->total_usd) !!}</td></tr>
@empty<tr><td colspan="2" class="da-empty">لا توجد مصروفات</td></tr>@endforelse
</tbody></table></div></section>
<section class="da-panel"><h2>المصروف حسب الشهر · {{ $filters['year'] }}</h2><div class="da-subscroll"><table><thead><tr><th>الشهر</th><th>الإجمالي</th></tr></thead><tbody>
@foreach($summary['months'] as $month)<tr><td>{{ $month['name'] }}</td><td class="da-money da-dual-money">{!! $dualMoney($month['total_iqd'], $month['total_usd']) !!}</td></tr>@endforeach
</tbody></table></div></section>
</div>
<div class="da-two">
<section class="da-panel"><h2>المصروف حسب الشخص</h2><div class="da-chart"><canvas id="daily-people-chart" role="img" aria-label="الرسم البياني للمصروف حسب الشخص">البيانات متاحة في جدول الأشخاص أعلاه.</canvas></div></section>
<section class="da-panel"><h2>المصروف حسب الشهر · {{ $filters['year'] }}</h2><div class="da-chart"><canvas id="daily-month-chart" role="img" aria-label="الرسم البياني للمصروف حسب الشهر">البيانات متاحة في جدول الأشهر أعلاه.</canvas></div></section>
</div>
<section class="da-panel"><h2>الحركات اليومية <small>({{ number_format($expenses->total()) }})</small></h2>
<div class="da-table-wrap"><table class="da-ledger"><thead><tr><th>التاريخ</th><th>اليوم</th><th>المبلغ المصروف</th><th>الجهة / الشخص</th><th>الملاحظات</th><th>رقم الشهر</th><th>الإجراءات</th></tr></thead><tbody>
@forelse($expenses as $row)
<tr><td>{{ $row->expense_date->format('Y-m-d') }}</td><td>{{ $row->expense_date->locale('ar')->translatedFormat('l') }}</td><td class="da-money"><span class="da-currency-line {{ strtolower($row->currency) }}">{{ $money($row->amount, $row->currency) }}</span></td><td>{{ $row->party->name }}</td><td class="da-note">{{ $row->notes ?: '—' }}</td><td>{{ $row->expense_date->month }}</td><td><div class="da-row-actions">
@can('sippar.daily_accounts.update')<a class="da-btn" href="{{ route('sippar.daily-accounts.edit', $row->id, false) }}">تعديل</a>@endcan
@can('sippar.daily_accounts.delete')<form method="post" action="{{ route('sippar.daily-accounts.destroy', $row->id, false) }}" data-confirm-delete>@csrf @method('DELETE')<button type="submit" class="da-danger">حذف</button></form>@endcan
</div></td></tr>
@empty<tr><td colspan="7" class="da-empty">لا توجد حركات مالية لشركة سيبار ضمن الفترة المحددة</td></tr>@endforelse
</tbody></table></div>
<nav class="da-pagination" aria-label="صفحات الحركات">
<span>عرض {{ $expenses->firstItem() ?? 0 }}–{{ $expenses->lastItem() ?? 0 }} من {{ $expenses->total() }}</span>
<div>@if($expenses->previousPageUrl())<a class="da-btn" href="{{ $expenses->previousPageUrl() }}">السابق</a>@endif <span>{{ $expenses->currentPage() }} / {{ $expenses->lastPage() }}</span> @if($expenses->hasMorePages())<a class="da-btn" href="{{ $expenses->nextPageUrl() }}">التالي</a>@endif</div>
</nav></section>
@can('sippar.daily_accounts.create')
<dialog id="expense-dialog" class="da-dialog"><h2>إضافة مصروف</h2><p>أضواء سيبار</p><form action="{{ route('sippar.daily-accounts.store', [], false) }}" method="post">@csrf
@include('daily_accounts._form')
@if($parties->where('is_active', true)->isEmpty())<p>أضف جهة / شخص أولاً من زر إضافة جهة.</p>@endif
<div class="da-actions"><button type="submit" class="da-primary">حفظ المصروف</button><button type="button" data-close-dialog>إلغاء</button></div>
</form></dialog>
<dialog id="party-dialog" class="da-dialog"><h2>إضافة جهة / شخص</h2><form method="post" action="{{ route('sippar.daily-accounts.parties.store', [], false) }}">@csrf<label>الاسم<input name="name" required maxlength="150" value="{{ old('name') }}"></label><div class="da-actions"><button type="submit" class="da-primary">حفظ الجهة</button><button type="button" data-close-dialog>إلغاء</button></div></form></dialog>
@endcan
</div>
<script src="/vendor/chartjs/chart.umd.min.js"></script>
<script>
(() => {
document.querySelectorAll('[data-dialog]').forEach(button => button.addEventListener('click', () => document.getElementById(button.dataset.dialog).showModal()));
document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
document.querySelectorAll('[data-confirm-delete]').forEach(form => form.addEventListener('submit', event => { if (!confirm('هل تريد حذف هذا المصروف؟ لا يمكن التراجع عن الحذف.')) event.preventDefault(); }));
@if($errors->any() && old('expense_date'))document.getElementById('expense-dialog')?.showModal();@endif
@if($errors->has('name'))document.getElementById('party-dialog')?.showModal();@endif
if (typeof Chart === 'undefined') return;
const people = @json($summary['people']->map(fn($p) => ['name' => $p->party->name, 'iqd' => (float) $p->total_iqd, 'usd' => (float) $p->total_usd])->values());
const months = @json($summary['months']);
const format = (value, currency) => new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 }).format(value) + ' ' + currency;
const draw = (id, labels, iqd, usd, horizontal) => new Chart(document.getElementById(id), {
type: 'bar', data: { labels, datasets: [{ label: 'IQD', data: iqd, backgroundColor: '#b6a082', borderRadius: 5, maxBarThickness: 28 }, { label: 'USD', data: usd, backgroundColor: '#597d69', borderRadius: 5, maxBarThickness: 28 }] },
options: { indexAxis: horizontal ? 'y' : 'x', responsive: true, maintainAspectRatio: false,
plugins: { legend: { display: true }, tooltip: { rtl: true, textDirection: 'rtl', callbacks: { label: ctx => format(horizontal ? ctx.parsed.x : ctx.parsed.y, ctx.dataset.label) } } },
scales: { x: { beginAtZero: true, grid: { color: '#f2eee7' } }, y: { beginAtZero: true, grid: { display: false } } } }
});
draw('daily-people-chart', people.map(p => p.name), people.map(p => p.iqd), people.map(p => p.usd), true);
draw('daily-month-chart', months.map(m => m.name), months.map(m => Number(m.total_iqd)), months.map(m => Number(m.total_usd)), false);
})();
</script>
@endsection
