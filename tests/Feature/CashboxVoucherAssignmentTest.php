<?php

namespace Tests\Feature;

use App\Models\Cashbox;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashboxVoucherAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_is_added_to_selected_cashbox_and_can_be_moved(): void
    {
        [$company, $user, $customer] = $this->records();
        $main = Cashbox::create(['company_id' => $company->id, 'name' => 'الرئيسي', 'balance' => 100]);
        $branch = Cashbox::create(['company_id' => $company->id, 'name' => 'الفرعي', 'balance' => 50]);

        $this->actingAs($user)->post('/receipts', [
            'receipt_date' => '2026-08-24', 'party_type' => 'customer', 'party_id' => $customer->id,
            'cashbox_id' => $branch->id, 'amount' => 200,
        ])->assertRedirect('/receipts');

        $receipt = $customer->receipts()->firstOrFail();
        $this->assertSame($branch->id, $receipt->cashbox_id);
        $this->assertSame(250.0, (float) $branch->fresh()->balance);

        $this->actingAs($user)->put('/receipts/'.$receipt->id, [
            'receipt_date' => '2026-08-24', 'party_type' => 'customer', 'party_id' => $customer->id,
            'cashbox_id' => $main->id, 'amount' => 300,
        ])->assertRedirect('/receipts');

        $this->assertSame(400.0, (float) $main->fresh()->balance);
        $this->assertSame(50.0, (float) $branch->fresh()->balance);
    }

    public function test_payment_is_deducted_from_selected_cashbox(): void
    {
        [$company, $user, $customer] = $this->records();
        $main = Cashbox::create(['company_id' => $company->id, 'name' => 'الرئيسي', 'balance' => 500]);

        $this->actingAs($user)->post('/payments', [
            'payment_date' => '2026-08-24', 'party_type' => 'customer', 'party_id' => $customer->id,
            'cashbox_id' => $main->id, 'amount' => 125,
        ])->assertRedirect('/payments');

        $this->assertDatabaseHas('payments', ['cashbox_id' => $main->id, 'amount' => 125]);
        $this->assertSame(375.0, (float) $main->fresh()->balance);
    }

    private function records(): array
    {
        $company = Company::create([
            'name' => 'شركة الصناديق', 'code' => uniqid('CB'), 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth(),
        ]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'زبون']);

        return [$company, $user, $customer];
    }
}
