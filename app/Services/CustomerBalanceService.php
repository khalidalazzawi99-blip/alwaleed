<?php

namespace App\Services;

use App\Models\Customer;

class CustomerBalanceService
{
    public function calculate(Customer $customer): array
    {
        $totalInvoices = (float) $customer->externalInvoices()->where('company_id', $customer->company_id)->where('status', '!=', 'cancelled')->sum('amount');
        $totalReceipts = (float) $customer->receipts()->active()->where('company_id', $customer->company_id)->sum('amount');
        $totalPayments = (float) $customer->payments()->active()->where('company_id', $customer->company_id)->sum('amount');
        $totalBorrowed = (float) $customer->debtTransactions()->where('company_id', $customer->company_id)->where('type', 'borrowing')->sum('amount');
        $totalDebtPaid = (float) $customer->debtTransactions()->where('company_id', $customer->company_id)->where('type', 'debt_payment')->sum('amount');
        $currentBalance = $totalInvoices + $totalPayments - $totalReceipts + $totalBorrowed - $totalDebtPaid;

        return compact('totalInvoices', 'totalReceipts', 'totalPayments', 'totalBorrowed', 'totalDebtPaid', 'currentBalance')
            + ['outstandingBalance' => $currentBalance];
    }
}
