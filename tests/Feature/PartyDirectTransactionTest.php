<?php

namespace Tests\Feature;

use App\Models\Cashbox;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartyDirectTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_borrowing_and_debt_payment_never_change_a_bank_balance(): void
    {
        [$user, $customer, , $bank] = $this->records();

        $this->actingAs($user)->get('/customers/'.$customer->id)
            ->assertOk()->assertSee('سداد ديون')->assertSee('استدانة')
            ->assertDontSee('الصندوق / الحساب');

        $this->postDebt('customer', $customer->id, 'borrowing', 75)
            ->assertRedirect('/customers/'.$customer->id);
        $this->postDebt('customer', $customer->id, 'debt_payment', 25)
            ->assertRedirect('/customers/'.$customer->id);

        $this->assertDatabaseHas('party_debt_transactions', ['customer_id' => $customer->id, 'type' => 'borrowing', 'amount' => 75]);
        $this->assertDatabaseHas('party_debt_transactions', ['customer_id' => $customer->id, 'type' => 'debt_payment', 'amount' => 25]);
        $this->assertDatabaseCount('receipts', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(100.0, (float) $bank->fresh()->balance);
        $this->get('/customers/'.$customer->id)->assertViewHas('balance', 50.0);
    }

    public function test_supplier_borrowing_and_debt_payment_never_change_a_bank_balance(): void
    {
        [$user, , $supplier, $bank] = $this->records();

        $this->actingAs($user)->get('/suppliers/'.$supplier->id)
            ->assertOk()->assertSee('سداد ديون')->assertSee('استدانة');

        $this->postDebt('supplier', $supplier->id, 'borrowing', 40)
            ->assertRedirect('/suppliers/'.$supplier->id);
        $this->postDebt('supplier', $supplier->id, 'debt_payment', 10)
            ->assertRedirect('/suppliers/'.$supplier->id);

        $this->assertDatabaseHas('party_debt_transactions', ['supplier_id' => $supplier->id, 'type' => 'borrowing', 'amount' => 40]);
        $this->assertDatabaseHas('party_debt_transactions', ['supplier_id' => $supplier->id, 'type' => 'debt_payment', 'amount' => 10]);
        $this->assertSame(100.0, (float) $bank->fresh()->balance);
        $this->get('/suppliers/'.$supplier->id)->assertViewHas('balance', 30.0);
    }

    private function postDebt(string $partyType, int $partyId, string $type, float $amount)
    {
        return $this->post('/party-debt-transactions', [
            'party_type' => $partyType,
            'party_id' => $partyId,
            'type' => $type,
            'transaction_date' => '2026-09-16',
            'amount' => $amount,
        ]);
    }

    private function records(): array
    {
        $company = Company::create([
            'name' => 'شركة تجريبية', 'code' => uniqid('PT'), 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth(),
        ]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'زبون تجريبي']);
        $supplier = Supplier::create(['company_id' => $company->id, 'name' => 'مورد تجريبي']);
        $bank = Cashbox::create(['company_id' => $company->id, 'name' => 'مصرف بغداد', 'account_type' => 'bank', 'balance' => 100]);

        return [$user, $customer, $supplier, $bank];
    }
}
