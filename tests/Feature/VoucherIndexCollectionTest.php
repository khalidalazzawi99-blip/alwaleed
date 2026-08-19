<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tests\TestCase;

class VoucherIndexCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_page_combines_supplier_and_customer_numbers_as_a_base_collection(): void
    {
        [$user, $customer, $supplier] = $this->companyParties();
        $year = now()->year;

        $this->actingAs($user)->get('/payments')
            ->assertOk()
            ->assertViewHas('nextPaymentNumbers', function ($numbers) use ($customer, $supplier, $year) {
                return $numbers instanceof Collection
                    && $numbers->get('supplier:'.$supplier->id) === "PAY-{$year}-000001"
                    && $numbers->get('customer:'.$customer->id) === "PAY-{$year}-000001";
            });
    }

    public function test_receipts_page_combines_customer_and_supplier_numbers_as_a_base_collection(): void
    {
        [$user, $customer, $supplier] = $this->companyParties();
        $year = now()->year;

        $this->actingAs($user)->get('/receipts')
            ->assertOk()
            ->assertViewHas('nextReceiptNumbers', function ($numbers) use ($customer, $supplier, $year) {
                return $numbers instanceof Collection
                    && $numbers->get('customer:'.$customer->id) === "RCP-{$year}-000001"
                    && $numbers->get('supplier:'.$supplier->id) === "RCP-{$year}-000001";
            });
    }

    private function companyParties(): array
    {
        $company = Company::create([
            'name' => 'Voucher Test Company',
            'code' => 'VCH'.Str::upper(Str::random(8)),
            'status' => 'active',
            'subscription_start' => now()->subDay(),
            'subscription_end' => now()->addYear(),
        ]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'Voucher Test Customer']);
        $supplier = Supplier::create(['company_id' => $company->id, 'name' => 'Voucher Test Supplier']);

        return [$user, $customer, $supplier];
    }
}
