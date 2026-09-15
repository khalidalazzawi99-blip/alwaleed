<?php

namespace Tests\Feature;

use App\Models\Cashbox;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialAccountArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_voucher_pages_never_creates_a_default_cashbox(): void
    {
        [$company, $user] = $this->records();

        $this->actingAs($user)->get('/receipts')->assertOk();
        $this->get('/payments')->assertOk();

        $this->assertSame(0, Cashbox::withTrashed()->where('company_id', $company->id)->count());
    }

    public function test_each_account_balance_is_independent_and_total_liquidity_is_the_sum(): void
    {
        [$company, $user] = $this->records();
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'زبون']);
        $baghdad = Cashbox::create(['company_id' => $company->id, 'name' => 'مصرف بغداد', 'account_type' => 'bank', 'balance' => 100]);
        $rafidain = Cashbox::create(['company_id' => $company->id, 'name' => 'الرافدين', 'account_type' => 'bank', 'balance' => 300]);

        $this->actingAs($user)->post('/receipts', [
            'receipt_date' => now()->toDateString(), 'party_type' => 'customer', 'party_id' => $customer->id,
            'cashbox_id' => $baghdad->id, 'amount' => 50,
        ])->assertRedirect('/receipts');

        $this->assertSame(150.0, (float) $baghdad->fresh()->balance);
        $this->assertSame(300.0, (float) $rafidain->fresh()->balance);
        $this->actingAs($user)->get('/cashbox')->assertOk()->assertViewHas('balance', 450.0);
    }

    public function test_zero_balance_bank_can_be_deleted_without_losing_linked_history(): void
    {
        [$company, $user] = $this->records();
        $bank = Cashbox::create(['company_id' => $company->id, 'name' => 'حساب قديم', 'account_type' => 'bank', 'balance' => 0]);

        $this->actingAs($user)->delete('/banks/'.$bank->id)->assertRedirect();

        $this->assertSoftDeleted('cashboxes', ['id' => $bank->id]);
    }

    private function records(): array
    {
        $company = Company::create(['name' => 'شركة', 'code' => uniqid('FA'), 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth()]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

        return [$company, $user];
    }
}
