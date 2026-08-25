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
        $request->validate(['updated_since' => ['nullable', 'date'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $company = $request->attributes->get('company');
        $currency = Setting::where('company_id', $company->id)->value('currency') ?: 'IQD';
        $page = Customer::where('company_id', $company->id)
            ->when($request->filled('updated_since'), fn ($query) => $query->where('updated_at', '>=', $request->date('updated_since')))
            ->orderBy('id')->paginate($this->perPage($request))->withQueryString();

        return response()->json([
            'data' => $page->getCollection()->map(fn (Customer $customer) => [
                'customer_id' => $customer->integration_id,
                'name' => $customer->name,
                'balance' => round($balances->calculate($customer)['outstandingBalance'], 2),
                'currency' => strtoupper($currency),
                'is_active' => true,
                'updated_at' => $customer->updated_at?->utc()->toIso8601String(),
            ])->values(),
            'meta' => $this->pagination($page),
        ]);
    }

    public function banks(Request $request)
    {
        $request->validate(['updated_since' => ['nullable', 'date'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $company = $request->attributes->get('company');
        $currency = Setting::where('company_id', $company->id)->value('currency') ?: 'IQD';
        $page = Cashbox::where('company_id', $company->id)
            ->when($request->filled('updated_since'), fn ($query) => $query->where('updated_at', '>=', $request->date('updated_since')))
            ->orderBy('id')->paginate($this->perPage($request))->withQueryString();

        return response()->json([
            'data' => $page->getCollection()->map(fn (Cashbox $cashbox) => [
                'bank_id' => $cashbox->integration_id,
                'name' => $cashbox->name,
                'balance' => round((float) $cashbox->balance, 2),
                'currency' => strtoupper($currency),
                'is_active' => (bool) $cashbox->is_active,
                'updated_at' => $cashbox->updated_at?->utc()->toIso8601String(),
            ])->values(),
            'meta' => $this->pagination($page),
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
        ];
    }
}
