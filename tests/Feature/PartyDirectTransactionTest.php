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

    public function test_customer_account_can_receive_deposits_and_withdrawals_in_place(): void
    {
        [$user, $customer, , $cashbox] = $this->records();

        $this->actingAs($user)->get('/customers/'.$customer->id)
            ->assertOk()->assertSee('إيداع في حساب')->assertSee('سحب من حساب');

        $this->post('/receipts', $this->transaction('customer', $customer->id, $cashbox->id, 75, true))
            ->assertRedirect('/customers/'.$customer->id);
        $this->post('/payments', $this->transaction('customer', $customer->id, $cashbox->id, 25, false))
            ->assertRedirect('/customers/'.$customer->id);

        $this->assertDatabaseHas('receipts', ['customer_id' => $customer->id, 'amount' => 75]);
        $this->assertDatabaseHas('payments', ['customer_id' => $customer->id, 'amount' => 25]);
        $this->assertSame(150.0, (float) $cashbox->fresh()->balance);
    }

    public function test_supplier_account_can_receive_deposits_and_withdrawals_in_place(): void
    {
        [$user, , $supplier, $cashbox] = $this->records();

        $this->actingAs($user)->get('/suppliers/'.$supplier->id)
            ->assertOk()->assertSee('إيداع في حساب')->assertSee('سحب من حساب');

        $this->post('/receipts', $this->transaction('supplier', $supplier->id, $cashbox->id, 40, true))
            ->assertRedirect('/suppliers/'.$supplier->id);
        $this->post('/payments', $this->transaction('supplier', $supplier->id, $cashbox->id, 10, false))
            ->assertRedirect('/suppliers/'.$supplier->id);

        $this->assertDatabaseHas('receipts', ['supplier_id' => $supplier->id, 'amount' => 40]);
        $this->assertDatabaseHas('payments', ['supplier_id' => $supplier->id, 'amount' => 10]);
        $this->assertSame(130.0, (float) $cashbox->fresh()->balance);
    }

    private function transaction(string $type, int $partyId, int $cashboxId, float $amount, bool $deposit): array
    {
        return [
            $deposit ? 'receipt_date' : 'payment_date' => '2026-09-15',
            'party_type' => $type,
            'party_id' => $partyId,
            'cashbox_id' => $cashboxId,
            'amount' => $amount,
            'redirect_to' => 'party',
        ];
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
        $cashbox = Cashbox::create(['company_id' => $company->id, 'name' => 'الصندوق', 'balance' => 100]);

        return [$user, $customer, $supplier, $cashbox];
    }
}
