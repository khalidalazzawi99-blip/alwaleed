<?php

namespace App\Services;

use App\Models\Customer;

class CustomerBalanceService
{
    public function calculate(Customer $customer, ?string $before = null): array
    {
        $totalInvoices = (float) $customer->externalInvoices()->where('company_id', $customer->company_id)->where('status', '!=', 'cancelled')
            ->when($before, fn ($query, $date) => $query->whereDate('invoice_date', '<', $date))->sum('amount');
        $totalReceipts = (float) $customer->receipts()->active()->where('company_id', $customer->company_id)
            ->when($before, fn ($query, $date) => $query->whereDate('receipt_date', '<', $date))->sum('amount');
        $totalPayments = (float) $customer->payments()->active()->where('company_id', $customer->company_id)
            ->when($before, fn ($query, $date) => $query->whereDate('payment_date', '<', $date))->sum('amount');
        $totalBorrowed = (float) $customer->debtTransactions()->where('company_id', $customer->company_id)->where('type', 'borrowing')
            ->when($before, fn ($query, $date) => $query->whereDate('transaction_date', '<', $date))->sum('amount');
        $totalDebtPaid = (float) $customer->debtTransactions()->where('company_id', $customer->company_id)->where('type', 'debt_payment')
            ->when($before, fn ($query, $date) => $query->whereDate('transaction_date', '<', $date))->sum('amount');
        $currentBalance = round($totalInvoices + $totalPayments - $totalReceipts + $totalBorrowed - $totalDebtPaid, 2);

        return compact('totalInvoices', 'totalReceipts', 'totalPayments', 'totalBorrowed', 'totalDebtPaid', 'currentBalance')
            + ['outstandingBalance' => $currentBalance];
    }
}
