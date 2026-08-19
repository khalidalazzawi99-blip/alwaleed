<style>
.statement-shell{display:grid;gap:20px}.statement-hero{position:relative;overflow:hidden;background:linear-gradient(135deg,#171f35,#27344f);color:#fff;border-radius:28px;padding:28px;display:flex;align-items:center;justify-content:space-between;gap:20px;box-shadow:0 20px 45px rgba(15,23,42,.15)}.statement-hero:after{content:"";position:absolute;width:260px;height:260px;border-radius:50%;background:rgba(205,186,158,.12);inset-inline-end:-90px;top:-130px}.statement-identity{display:flex;align-items:center;gap:18px;position:relative;z-index:1}.statement-avatar{width:68px;height:68px;border-radius:21px;background:linear-gradient(135deg,#dccbae,#bca17d);display:grid;place-items:center;color:#172039;font-size:28px;font-weight:900}.statement-hero h1{margin:0 0 5px;font-size:27px}.statement-hero p{margin:0;color:#cbd5e1}.statement-actions{display:flex;gap:10px;position:relative;z-index:1}.statement-actions .btn{background:#fff;color:#172039}.statement-filter{display:grid;grid-template-columns:1fr 1fr auto auto;gap:12px;align-items:end;background:var(--surface);border:1px solid var(--border);border-radius:22px;padding:18px}.statement-filter label{display:block;font-size:12px;font-weight:800;color:var(--text-soft);margin-bottom:7px}.statement-filter .clear-btn{background:var(--surface-soft);color:var(--text);border:1px solid var(--border)}.statement-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:15px}.statement-kpi{background:var(--surface);border:1px solid var(--border);border-radius:22px;padding:20px;box-shadow:var(--shadow-soft)}.statement-kpi span{display:block;color:var(--text-soft);font-size:13px;font-weight:700}.statement-kpi strong{display:block;margin-top:10px;font-size:25px}.statement-kpi.received strong{color:#15803d}.statement-kpi.paid strong{color:#b91c1c}.statement-kpi.balance strong.positive{color:#15803d}.statement-kpi.balance strong.negative{color:#b91c1c}.statement-details{display:grid;grid-template-columns:1.1fr .9fr;gap:18px}.statement-panel{background:var(--surface);border:1px solid var(--border);border-radius:22px;padding:21px}.statement-panel h2{font-size:17px;margin:0 0 17px}.detail-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.detail-item{background:var(--surface-soft);border:1px solid var(--border);border-radius:16px;padding:14px}.detail-item span{display:block;color:var(--text-soft);font-size:12px;margin-bottom:6px}.detail-item strong{word-break:break-word}.party-notes{line-height:1.8;white-space:pre-wrap;color:var(--text)}.statement-table-card{background:var(--surface);border:1px solid var(--border);border-radius:24px;padding:20px;overflow:hidden}.statement-table-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}.statement-table-head h2{margin:0;font-size:19px}.statement-period{font-size:12px;color:var(--text-soft);background:var(--surface-soft);padding:7px 11px;border-radius:10px}.statement-table-wrap{overflow-x:auto}.statement-table{min-width:880px}.statement-table td,.statement-table th{white-space:nowrap}.statement-table .notes-cell{white-space:normal;min-width:220px;line-height:1.6}.money-in{color:#15803d;font-weight:800}.money-out{color:#b91c1c;font-weight:800}.balance-value{font-weight:900}.empty-row{text-align:center!important;color:var(--text-soft);padding:35px!important}.statement-table tfoot td{font-weight:900;background:var(--surface)!important;border-top:2px solid var(--border)}
@media(max-width:900px){.statement-hero{align-items:flex-start;flex-direction:column}.statement-filter{grid-template-columns:1fr 1fr}.statement-kpis{grid-template-columns:repeat(2,1fr)}.statement-details{grid-template-columns:1fr}}@media(max-width:560px){.statement-filter,.statement-kpis,.detail-grid{grid-template-columns:1fr}.statement-actions{width:100%}.statement-actions .btn{width:100%;text-align:center}.statement-avatar{width:55px;height:55px}.statement-hero{padding:21px}}
</style>
<style>.statement-kpis{grid-template-columns:repeat(5,1fr)}@media(max-width:1100px){.statement-kpis{grid-template-columns:repeat(3,1fr)}}@media(max-width:560px){.statement-kpis{grid-template-columns:1fr}}</style>

<div class="statement-shell">
    <section class="statement-hero">
        <div class="statement-identity">
            <div class="statement-avatar">{{ mb_substr($party->name, 0, 1) }}</div>
            <div>
                <h1>{{ $party->name }}</h1>
                <p>{{ $statementTitle }} · {{ __('messages.statement_subtitle') }}</p>
            </div>
        </div>
        <div class="statement-actions">
            @php $query = http_build_query(array_filter(['from' => $from, 'to' => $to])); @endphp
            <a class="btn" href="{{ $receiptUrl }}">{{ __('messages.new_receipt') }}</a>
            <a class="btn" href="{{ $paymentUrl }}">{{ __('messages.new_payment') }}</a>
            <a class="btn" target="_blank" href="{{ $printUrl }}?{{ $query }}">{{ __('messages.print') }}</a>
            <a class="btn" href="{{ str_replace('/print', '/pdf', $printUrl) }}?{{ $query }}">{{ __('messages.export_pdf') }}</a>
            <a class="btn" href="{{ str_replace('/print', '/excel', $printUrl) }}?{{ $query }}">{{ __('messages.export_excel') }}</a>
        </div>
    </section>

    <form method="GET" class="statement-filter">
        <div><label>{{ __('messages.from_date') }}</label><input type="date" name="from" value="{{ $from }}"></div>
        <div><label>{{ __('messages.to_date') }}</label><input type="date" name="to" value="{{ $to }}"></div>
        <button type="submit">{{ __('messages.filter') }}</button>
        <a href="{{ $resetUrl }}" class="btn clear-btn">{{ __('messages.clear') }}</a>
    </form>

    <section class="statement-kpis">
        <div class="statement-kpi"><span>{{ __('messages.total_invoiced') }}</span><strong>{{ number_format($totalInvoiced, 2) }} {{ $companyCurrency }}</strong></div>
        <div class="statement-kpi received"><span>{{ __('messages.total_received') }}</span><strong>{{ number_format($totalReceived, 2) }} {{ $companyCurrency }}</strong></div>
        <div class="statement-kpi paid"><span>{{ __('messages.total_paid') }}</span><strong>{{ number_format($totalPaid, 2) }} {{ $companyCurrency }}</strong></div>
        <div class="statement-kpi balance"><span>{{ __('messages.current_balance') }}</span><strong class="{{ $balance >= 0 ? 'positive' : 'negative' }}">{{ number_format($balance, 2) }} {{ $companyCurrency }}</strong></div>
        <div class="statement-kpi"><span>{{ __('messages.movements_count') }}</span><strong>{{ $movementsCount }}</strong></div>
    </section>

    <section class="statement-details">
        <div class="statement-panel">
            <h2>{{ __('messages.contact_information') }}</h2>
            <div class="detail-grid">
                <div class="detail-item"><span>{{ __('messages.phone') }}</span><strong>{{ $party->phone ?: '-' }}</strong></div>
                <div class="detail-item"><span>{{ __('messages.party_company') }}</span><strong>{{ $party->company_name ?: '-' }}</strong></div>
                <div class="detail-item" style="grid-column:1/-1"><span>{{ __('messages.address') }}</span><strong>{{ $party->address ?: '-' }}</strong></div>
            </div>
        </div>
        <div class="statement-panel">
            <h2>{{ __('messages.general_notes') }}</h2>
            <div class="party-notes">{{ $party->notes ?: __('messages.no_notes') }}</div>
        </div>
    </section>

    <section class="statement-table-card">
        <div class="statement-table-head">
            <h2>{{ __('messages.movement_history') }}</h2>
            <span class="statement-period">{{ $from || $to ? (($from ?: '…').' — '.($to ?: '…')) : __('messages.all_dates') }}</span>
        </div>
        <div class="statement-table-wrap">
            <table class="statement-table">
                <thead><tr><th>#</th><th>{{ __('messages.date') }}</th><th>{{ __('messages.reference') }}</th><th>{{ __('messages.movement_type') }}</th><th>{{ __('messages.invoiced') }}</th><th>{{ __('messages.received') }}</th><th>{{ __('messages.paid') }}</th><th>{{ __('messages.running_balance') }}</th><th>{{ __('messages.notes') }}</th></tr></thead>
                <tbody>
                @forelse($movements as $index => $movement)
                    <tr><td>{{ $index + 1 }}</td><td>{{ $movement->date }}</td><td>{{ $movement->number }}</td><td>{{ $movement->type }}</td><td>{{ $movement->invoiced ? number_format($movement->invoiced, 2) : '—' }}</td><td class="money-in">{{ $movement->received ? number_format($movement->received, 2) : '—' }}</td><td class="money-out">{{ $movement->paid ? number_format($movement->paid, 2) : '—' }}</td><td class="balance-value">{{ number_format($movement->balance, 2) }}</td><td class="notes-cell">{{ $movement->notes ?: '—' }}</td></tr>
                @empty
                    <tr><td colspan="9" class="empty-row">{{ __('messages.no_movements') }}</td></tr>
                @endforelse
                </tbody>
                <tfoot><tr><td colspan="4">{{ __('messages.total') }}</td><td>{{ number_format($totalInvoiced, 2) }}</td><td class="money-in">{{ number_format($totalReceived, 2) }}</td><td class="money-out">{{ number_format($totalPaid, 2) }}</td><td>{{ number_format($balance, 2) }}</td><td></td></tr></tfoot>
            </table>
        </div>
    </section>
</div>
