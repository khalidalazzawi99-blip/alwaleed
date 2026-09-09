<table class="finance-statement-table">
    <thead><tr><th>#</th><th>{{ __('messages.date') }}</th><th>{{ __('messages.statement_details') }}</th><th>{{ __('messages.reference') }}</th><th class="numeric">{{ __('messages.statement_incoming') }}</th><th class="numeric">{{ __('messages.statement_outgoing') }}</th><th class="numeric">{{ __('messages.running_balance') }}</th></tr></thead>
    <tbody>
        <tr class="statement-opening"><td colspan="6">{{ __('messages.period_opening_balance') }}@if($from) · <bdi>{{ $from }}</bdi>@endif</td><td class="numeric balance-cell"><bdi>{{ number_format($openingBalance, 2) }}</bdi></td></tr>
        @forelse($movements as $index => $movement)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="statement-date"><bdi>{{ $movement->date }}</bdi></td>
                <td class="statement-description"><strong>{{ $movement->type }}</strong>@if($movement->party)<small>{{ $movement->party }}</small>@endif @if($movement->notes)<small>{{ $movement->notes }}</small>@endif</td>
                <td class="statement-reference"><bdi>{{ $movement->reference ?: '—' }}</bdi></td>
                <td class="numeric finance-in"><bdi>{{ $movement->incoming ? number_format($movement->incoming, 2) : '—' }}</bdi></td>
                <td class="numeric finance-out"><bdi>{{ $movement->outgoing ? number_format($movement->outgoing, 2) : '—' }}</bdi></td>
                <td class="numeric balance-cell"><bdi>{{ number_format($movement->balance, 2) }}</bdi></td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;padding:32px">{{ __('messages.no_period_movements') }}</td></tr>
        @endforelse
    </tbody>
    <tfoot><tr><td colspan="4">{{ __('messages.period_totals') }}</td><td class="numeric finance-in"><bdi>{{ number_format($totalIncoming, 2) }}</bdi></td><td class="numeric finance-out"><bdi>{{ number_format($totalOutgoing, 2) }}</bdi></td><td class="numeric balance-cell"><bdi>{{ number_format($closingBalance, 2) }}</bdi></td></tr></tfoot>
</table>
