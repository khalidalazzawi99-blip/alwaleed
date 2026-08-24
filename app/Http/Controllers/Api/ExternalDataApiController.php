<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cashbox;
use App\Models\Customer;
use App\Models\Setting;
use App\Services\CustomerBalanceService;
use Illuminate\Http\Request;

class ExternalDataApiController extends Controller
{
    public function customers(Request $request, CustomerBalanceService $balances)
    {
        $company = $request->attributes->get('company');
        $currency = Setting::where('company_id', $company->id)->value('currency') ?: 'IQD';
        $page = Customer::where('company_id', $company->id)->orderBy('id')
            ->paginate($this->perPage($request));

        return response()->json([
            'customers' => $page->getCollection()->map(fn (Customer $customer) => [
                'external_customer_id' => $customer->external_customer_id,
                'name' => $customer->name,
                'balance' => round($balances->calculate($customer)['outstandingBalance'], 2),
                'currency' => strtoupper($currency),
                'is_active' => true,
                'updated_at' => $customer->updated_at?->utc()->toIso8601String(),
            ])->values(),
            'pagination' => $this->pagination($page),
        ]);
    }

    public function banks(Request $request)
    {
        $company = $request->attributes->get('company');
        $currency = Setting::where('company_id', $company->id)->value('currency') ?: 'IQD';
        $page = Cashbox::where('company_id', $company->id)->orderBy('id')
            ->paginate($this->perPage($request));

        return response()->json([
            'banks' => $page->getCollection()->map(fn (Cashbox $cashbox) => [
                'external_bank_id' => 'B-'.$cashbox->id,
                'name' => $cashbox->name,
                'balance' => round((float) $cashbox->balance, 2),
                'currency' => strtoupper($currency),
                'is_active' => (bool) $cashbox->is_active,
                'updated_at' => $cashbox->updated_at?->utc()->toIso8601String(),
            ])->values(),
            'pagination' => $this->pagination($page),
        ]);
    }

    private function perPage(Request $request): int
    {
        return max(1, min($request->integer('per_page', 100), 100));
    }

    private function pagination($page): array
    {
        return [
            'current_page' => $page->currentPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
            'last_page' => $page->lastPage(),
            'next_page_url' => $page->nextPageUrl(),
            'prev_page_url' => $page->previousPageUrl(),
        ];
    }
}
