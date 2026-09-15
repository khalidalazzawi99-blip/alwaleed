<?php

namespace App\Services;

use App\Models\Cashbox;
use App\Models\CashboxLog;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\DailyExpense;
use Illuminate\Support\Collection;

class CashboxStatementService
{
    public function calculate(Cashbox $cashbox, ?string $from = null, ?string $to = null): array
    {
        $movements = $this->movements($cashbox);
        // Use the effective vouchers rather than audit entries for edits/deletions.
        // The difference preserves the account's opening funds, including older accounts.
        $initialCents = $this->cents($cashbox->balance)
            - $movements->sum(fn ($row) => $row->incomingCents - $row->outgoingCents);
        $openingCents = $initialCents;
        if ($from) {
            $openingCents += $movements->where('date', '<', $from)
                ->sum(fn ($row) => $row->incomingCents - $row->outgoingCents);
        }
        $period = $movements
            ->filter(fn ($row) => (!$from || $row->date >= $from) && (!$to || $row->date <= $to))
            ->values();
        $runningCents = $openingCents;
        foreach ($period as $row) {
            $runningCents += $row->incomingCents - $row->outgoingCents;
            $row->incoming = $row->incomingCents / 100;
            $row->outgoing = $row->outgoingCents / 100;
            $row->balance = $runningCents / 100;
        }

        return [
            'cashbox' => $cashbox,
            'from' => $from,
            'to' => $to,
            'movements' => $period,
            'openingBalance' => $openingCents / 100,
            'closingBalance' => $runningCents / 100,
            'totalIncoming' => $period->sum('incomingCents') / 100,
            'totalOutgoing' => $period->sum('outgoingCents') / 100,
        ];
    }

    private function movements(Cashbox $cashbox): Collection
    {
        $receipts = Receipt::with(['customer', 'supplier'])->where('company_id', $cashbox->company_id)
            ->where('cashbox_id', $cashbox->id)->get();
        $payments = Payment::with(['customer', 'supplier'])->where('company_id', $cashbox->company_id)
            ->where('cashbox_id', $cashbox->id)->get();
        $dailyExpenses = DailyExpense::with('party')->where('company_id', $cashbox->company_id)
            ->where('cashbox_id', $cashbox->id)->get();
        $logs = CashboxLog::where('company_id', $cashbox->company_id)->where('cashbox_id', $cashbox->id)
            ->whereIn('type', ['إيداع مباشر', 'سحب مباشر'])->get();

        $rows = collect();
        foreach ($receipts as $receipt) {
            $rows->push($this->row('receipt', $receipt->id, $receipt->receipt_date ?: $receipt->created_at->toDateString(),
                $receipt->created_at->format('H:i:s'), $receipt->receipt_no, __('messages.received'),
                $receipt->party?->name, $receipt->notes, $this->cents($receipt->amount), 0));
        }
        foreach ($payments as $payment) {
            $rows->push($this->row('payment', $payment->id, $payment->payment_date ?: $payment->created_at->toDateString(),
                $payment->created_at->format('H:i:s'), $payment->payment_no, __('messages.paid'),
                $payment->party?->name, $payment->notes, 0, $this->cents($payment->amount)));
        }
        foreach ($dailyExpenses as $expense) {
            $rows->push($this->row('daily-expense', $expense->id, $expense->expense_date->toDateString(),
                $expense->created_at->format('H:i:s'), 'DAILY-'.$expense->id, 'مصروف يومي',
                $expense->party?->name, $expense->notes, 0, $this->cents($expense->amount)));
        }
        foreach ($logs as $log) {
            $incoming = $log->type === 'إيداع مباشر';
            $rows->push($this->row('transaction', $log->id, $log->created_at->toDateString(),
                $log->created_at->format('H:i:s'), $log->reference_no,
                __($incoming ? 'messages.deposit' : 'messages.withdrawal'), $log->person_name, $log->notes,
                $incoming ? $this->cents($log->amount) : 0, $incoming ? 0 : $this->cents($log->amount)));
        }

        return $rows->sortBy('sortKey')->values();
    }

    private function row(string $source, int $id, string $date, string $time, ?string $reference,
        string $type, ?string $party, ?string $notes, int $incomingCents, int $outgoingCents): object
    {
        return (object) [
            'date' => substr($date, 0, 10),
            'reference' => $reference,
            'type' => $type,
            'party' => $party,
            'notes' => $notes,
            'incomingCents' => $incomingCents,
            'outgoingCents' => $outgoingCents,
            'sortKey' => substr($date, 0, 10).' '.$time.' '.$source.' '.str_pad((string) $id, 20, '0', STR_PAD_LEFT),
        ];
    }

    private function cents(mixed $amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
