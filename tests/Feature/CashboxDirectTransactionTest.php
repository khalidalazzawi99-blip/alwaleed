<?php

namespace Tests\Feature;

use App\Models\Cashbox;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashboxDirectTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_user_can_deposit_and_withdraw_directly(): void
    {
        [$company, $user] = $this->records();
        $cashbox = Cashbox::create(['company_id' => $company->id, 'name' => 'الرئيسي', 'balance' => 100, 'is_active' => true]);

        $this->actingAs($user)->post("/cashbox/{$cashbox->id}/transactions", [
            'type' => 'deposit', 'amount' => 50, 'notes' => 'إيداع تجريبي',
        ])->assertRedirect();
        $this->assertSame(150.0, (float) $cashbox->fresh()->balance);

        $this->actingAs($user)->post("/cashbox/{$cashbox->id}/transactions", [
            'type' => 'withdrawal', 'amount' => 40, 'notes' => 'سحب تجريبي',
        ])->assertRedirect();
        $this->assertSame(110.0, (float) $cashbox->fresh()->balance);
        $this->assertDatabaseHas('cashbox_logs', ['cashbox_id' => $cashbox->id, 'type' => 'إيداع مباشر', 'amount' => 50]);
        $this->assertDatabaseHas('cashbox_logs', ['cashbox_id' => $cashbox->id, 'type' => 'سحب مباشر', 'amount' => 40, 'balance_after' => 110]);
    }

    public function test_withdrawal_cannot_exceed_balance_or_access_another_company_cashbox(): void
    {
        [$company, $user] = $this->records();
        [$otherCompany] = $this->records('OTHER');
        $cashbox = Cashbox::create(['company_id' => $company->id, 'name' => 'الرئيسي', 'balance' => 100, 'is_active' => true]);
        $otherCashbox = Cashbox::create(['company_id' => $otherCompany->id, 'name' => 'الآخر', 'balance' => 100, 'is_active' => true]);

        $this->actingAs($user)->from('/cashbox')->post("/cashbox/{$cashbox->id}/transactions", [
            'type' => 'withdrawal', 'amount' => 101,
        ])->assertRedirect('/cashbox')->assertSessionHasErrors('amount');
        $this->assertSame(100.0, (float) $cashbox->fresh()->balance);

        $this->actingAs($user)->post("/cashbox/{$otherCashbox->id}/transactions", [
            'type' => 'deposit', 'amount' => 50,
        ])->assertNotFound();
        $this->assertSame(100.0, (float) $otherCashbox->fresh()->balance);
    }

    private function records(string $suffix = ''): array
    {
        $company = Company::create([
            'name' => 'شركة '.$suffix, 'code' => uniqid('CBD'), 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth(),
        ]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

        return [$company, $user];
    }
}
