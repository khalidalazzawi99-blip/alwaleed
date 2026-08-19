@extends('layouts.app')
@section('content')
<style>
.ext-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}.ext-card{background:var(--surface);border:1px solid var(--border);border-radius:22px;padding:21px}.ext-card h2{margin-top:0}.ext-form{display:grid;gap:12px}.ext-form.two{grid-template-columns:1fr 1fr}.api-token{direction:ltr;word-break:break-all;background:#172039;color:#f8e8ca;padding:15px;border-radius:14px;font-family:monospace}.status-pill{padding:5px 9px;border-radius:999px;font-size:11px;font-weight:800;background:var(--surface-soft)}.api-doc{direction:ltr;text-align:left;background:#172039;color:#dbe4f4;padding:14px;border-radius:14px;overflow:auto;font:12px/1.8 monospace}.full{grid-column:1/-1}@media(max-width:850px){.ext-grid,.ext-form.two{grid-template-columns:1fr}.full{grid-column:auto}}
</style>
<div class="topbar"><div><h1 class="page-title">{{ __('messages.external_invoices') }}</h1><p>{{ __('messages.external_invoices_subtitle') }}</p></div></div>
@if(session('success'))<div class="card" style="background:#ecfdf3;color:#166534">{{ session('success') }}</div>@endif
@if(session('api_token_plain'))<div class="card"><strong>{{ __('messages.copy_api_token_now') }}</strong><div class="api-token">{{ session('api_token_plain') }}</div></div>@endif
<div class="ext-grid">
 <section class="ext-card"><h2>{{ __('messages.integration_settings') }}</h2><form class="ext-form" method="POST" action="{{ route('external-invoices.integration') }}">@csrf
  <label><input style="width:auto" type="checkbox" name="enabled" value="1" @checked(old('enabled',$integration->enabled ?? true))> {{ __('messages.enabled') }}</label><button>{{ __('messages.save') }}</button>
  </form><div style="display:grid;gap:8px;margin-top:16px"><span>{{ __('messages.api_connection_status') }}: <b>{{ $integration->enabled && $hasActiveToken ? __('messages.active') : __('messages.disabled') }}</b></span><span>{{ __('messages.last_successful_invoice') }}: <b>{{ $integration->last_sync_at?->format('Y/m/d H:i') ?: '-' }}</b></span><span>{{ __('messages.last_api_activity') }}: <b>{{ $lastActivity?->created_at?->format('Y/m/d H:i') ?: '-' }}</b></span><span>{{ __('messages.external_invoice_count') }}: <b>{{ $invoiceCount }}</b></span></div></section>
 <section class="ext-card"><h2>{{ __('messages.customer_linking') }}</h2><form class="ext-form" method="POST" action="{{ route('external-invoices.customer-link') }}">@csrf
  <input name="external_customer_id" placeholder="External customer ID" required>
  <select name="customer_id" required><option value="">{{ __('messages.choose_customer') }}</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select><button>{{ __('messages.link_customer') }}</button>
 </form><div style="margin-top:14px">@forelse($customers->whereNotNull('external_customer_id') as $customer)<span class="status-pill">{{ $customer->external_customer_id }} → {{ $customer->name }}</span> @empty <p>{{ __('messages.no_customer_links') }}</p>@endforelse</div></section>
 <section class="ext-card"><h2>{{ __('messages.api_security') }}</h2><form class="ext-form two" method="POST" action="{{ route('external-invoices.tokens.create') }}">@csrf
  <input name="name" placeholder="{{ __('messages.token_name') }}" required><input type="date" name="expires_at"><button class="full">{{ __('messages.generate_api_token') }}</button></form>
  <div style="margin-top:14px">@foreach($tokens as $token)<div style="display:flex;justify-content:space-between;gap:10px;align-items:center;margin-top:8px"><span><b>{{ $token->name }}</b> · {{ $token->token_prefix }}… · {{ $token->revoked_at ? __('messages.revoked') : __('messages.active') }}</span>@unless($token->revoked_at)<form method="POST" action="{{ route('external-invoices.tokens.revoke',$token) }}">@csrf @method('DELETE')<button class="danger">{{ __('messages.revoke') }}</button></form>@endunless</div>@endforeach</div>
 </section>
 <section class="ext-card"><h2>API v1</h2><div class="api-doc">Authorization: Bearer aw_live_...
POST /api/v1/external-invoices</div></section>
 <section class="ext-card full"><h2>{{ __('messages.synced_invoices') }}</h2><div style="overflow:auto"><table><thead><tr><th>{{ __('messages.reference') }}</th><th>{{ __('messages.customer') }}</th><th>{{ __('messages.date') }}</th><th>{{ __('messages.amount') }}</th></tr></thead><tbody>@forelse($invoices as $invoice)<tr><td>{{ $invoice->invoice_no }}</td><td>{{ $invoice->customer?->name ?: '-' }}</td><td>{{ $invoice->invoice_date?->format('Y/m/d') }}</td><td>{{ number_format($invoice->amount,2) }}</td></tr>@empty<tr><td colspan="4">{{ __('messages.no_external_invoices') }}</td></tr>@endforelse</tbody></table></div>{{ $invoices->links() }}</section>
 @if($recentErrors->isNotEmpty())<section class="ext-card full"><h2>{{ __('messages.recent_integration_errors') }}</h2>@foreach($recentErrors as $error)<div class="status-pill" style="display:block;margin-top:8px">{{ $error->created_at->format('Y/m/d H:i') }} · {{ $error->http_status }} · {{ $error->error_message }}</div>@endforeach</section>@endif
</div>
@endsection
