<?php
namespace App\Services;
use App\Models\Customer;
class CustomerBalanceService {
    public function calculate(Customer $customer): array {
        $totalInvoices=(float)$customer->externalInvoices()->where('company_id',$customer->company_id)->where('status','!=','cancelled')->sum('amount');
        $totalReceipts=(float)$customer->receipts()->where('company_id',$customer->company_id)->sum('amount');
        $totalPayments=(float)$customer->payments()->where('company_id',$customer->company_id)->sum('amount');
        $currentBalance=$totalInvoices > 0
            ? $totalInvoices + $totalPayments - $totalReceipts
            : $totalReceipts - $totalPayments;
        return compact('totalInvoices','totalReceipts','totalPayments','currentBalance')
            + ['outstandingBalance'=>$currentBalance];
    }
}
