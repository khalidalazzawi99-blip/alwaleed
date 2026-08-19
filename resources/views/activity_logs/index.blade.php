@extends('layouts.app')

@section('content')
<style>
.audit-wrap{display:grid;gap:18px}.audit-hero{background:linear-gradient(135deg,#172139,#293853);color:#fff;border-radius:26px;padding:25px;display:flex;justify-content:space-between;align-items:center;gap:15px}.audit-hero h1{margin:0 0 5px;font-size:27px}.audit-hero p{margin:0;color:#cbd5e1}.audit-lock{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.14);padding:11px 14px;border-radius:14px;font-size:12px}.audit-filter{display:grid;grid-template-columns:1fr 190px auto;gap:10px;background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:15px}.audit-list{display:grid;gap:12px}.audit-item{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:18px;display:grid;grid-template-columns:165px 1fr;gap:18px}.audit-meta{border-inline-end:1px solid var(--border);padding-inline-end:15px}.audit-event{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:900;margin-bottom:12px}.audit-event.created{background:rgba(21,128,61,.1);color:#15803d}.audit-event.updated{background:rgba(217,119,6,.1);color:#b76a05}.audit-event.deleted{background:rgba(220,38,38,.1);color:#dc2626}.audit-user{font-weight:900}.audit-time,.audit-ip{font-size:11px;color:var(--text-soft);margin-top:5px}.audit-subject{font-size:15px;font-weight:900;margin-bottom:11px}.changes-grid{display:grid;gap:7px}.change-row{display:grid;grid-template-columns:minmax(120px,.65fr) 1fr 24px 1fr;gap:9px;align-items:center;background:var(--surface-soft);border:1px solid var(--border);border-radius:12px;padding:9px 11px;font-size:12px}.change-field{font-weight:900;color:var(--text-soft)}.change-value{word-break:break-word}.change-arrow{text-align:center;color:var(--text-soft)}.audit-empty{text-align:center;padding:45px;background:var(--surface);border-radius:20px;color:var(--text-soft)}.audit-pagination nav{display:flex;justify-content:center}.audit-pagination svg{width:18px}.audit-pagination p{display:none}
@media(max-width:700px){.audit-hero{align-items:flex-start;flex-direction:column}.audit-filter{grid-template-columns:1fr}.audit-item{grid-template-columns:1fr}.audit-meta{border-inline-end:0;border-bottom:1px solid var(--border);padding:0 0 12px}.change-row{grid-template-columns:1fr}.change-arrow{transform:rotate(90deg)}}
</style>

<div class="audit-wrap">
    <header class="audit-hero">
        <div><h1>{{ __('messages.audit_log') }}</h1><p>{{ __('messages.audit_description') }}</p></div>
        <div class="audit-lock">🔒 {{ __('messages.immutable_log') }}</div>
    </header>

    <form method="GET" class="audit-filter">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.audit_search') }}">
        <select name="event">
            <option value="">{{ __('messages.all_events') }}</option>
            @foreach(['created','updated','deleted'] as $event)
                <option value="{{ $event }}" @selected(request('event') === $event)>{{ __('messages.'.$event) }}</option>
            @endforeach
        </select>
        <button type="submit">{{ __('messages.filter') }}</button>
    </form>

    <div class="audit-list">
    @forelse($logs as $log)
        @php
            $event = $log->event ?: $log->action;
            $old = $log->old_values ?? [];
            $new = $log->new_values ?? [];
            $fields = array_unique(array_merge(array_keys($old), array_keys($new)));
            $formatAuditValue = function ($value) {
                if (is_bool($value)) return $value ? 'true' : 'false';
                if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE);
                return $value === null || $value === '' ? '—' : (string) $value;
            };
        @endphp
        <article class="audit-item">
            <aside class="audit-meta">
                <span class="audit-event {{ $event }}">{{ in_array($event, ['created','updated','deleted']) ? __('messages.'.$event) : __($event) }}</span>
                <div class="audit-user">{{ $log->user_name }}</div>
                <div class="audit-time">{{ $log->created_at?->format('Y/m/d H:i:s') }}</div>
                <div class="audit-ip">{{ $log->ip_address ?: '—' }}</div>
            </aside>
            <section>
                <div class="audit-subject">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : $log->details }}</div>
                @if($fields)
                    <div class="changes-grid">
                    @foreach($fields as $field)
                        <div class="change-row">
                            <span class="change-field">{{ $field }}</span>
                            <span class="change-value">{{ $formatAuditValue($old[$field] ?? null) }}</span>
                            <span class="change-arrow">←</span>
                            <span class="change-value">{{ $formatAuditValue($new[$field] ?? null) }}</span>
                        </div>
                    @endforeach
                    </div>
                @else
                    <div style="color:var(--text-soft)">{{ $log->details ?: '—' }}</div>
                @endif
            </section>
        </article>
    @empty
        <div class="audit-empty">{{ __('messages.no_audit_logs') }}</div>
    @endforelse
    </div>

    <div class="audit-pagination">{{ $logs->links() }}</div>
</div>
@endsection
