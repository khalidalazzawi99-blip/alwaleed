<?php
namespace App\Services;
use App\Models\Customer;
class CustomerBalanceService {
    public function calculate(Customer $customer): array {
        $totalInvoices=(float)$customer->externalInvoices()->where('company_id',$customer->company_id)->sum('amount');
        $totalReceipts=(float)$customer->receipts()->where('company_id',$customer->company_id)->sum('amount');
        return compact('totalInvoices','totalReceipts')+['outstandingBalance'=>$totalInvoices-$totalReceipts];
    }
}
