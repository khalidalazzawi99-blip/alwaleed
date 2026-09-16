<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VoucherIndexCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_page_uses_one_company_wide_sequence_preview(): void
    {
        [$user] = $this->companyParties();
        $year = now()->year;

        $this->actingAs($user)->get('/payments')
            ->assertOk()
            ->assertViewHas('nextPaymentNo', "PAY-{$year}-000001");
    }

    public function test_receipts_page_uses_one_company_wide_sequence_preview(): void
    {
        [$user] = $this->companyParties();
        $year = now()->year;

        $this->actingAs($user)->get('/receipts')
            ->assertOk()
            ->assertViewHas('nextReceiptNo', "RCP-{$year}-000001");
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
