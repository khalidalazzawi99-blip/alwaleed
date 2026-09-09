<?php

namespace Tests\Feature;

use App\Models\{Cashbox, CashboxLog, Company, Customer, Receipt, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_can_be_created_edited_and_selected_without_multiple_cashboxes_feature(): void
    {
        [$company, $user, $customer, $cash] = $this->records();
        $this->actingAs($user)->get('/banks')->assertOk()->assertSee('إضافة بنك');
        $this->actingAs($user)->post('/banks', [
            'name' => 'مصرف الرافدين', 'account_number' => 'IQ123', 'balance' => 1000,
            'account_type' => 'cash', 'customer_ids' => [$customer->id],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $bank = Cashbox::where('account_type', 'bank')->sole();
        $this->assertSame($company->id, $bank->company_id);
        $this->assertSame('مصرف الرافدين', $bank->bank_name);
        $this->assertTrue($bank->customers->contains($customer));
        $this->actingAs($user)->get('/cashbox')->assertOk()->assertSee('مصرف الرافدين')
            ->assertDontSee('إيداع مباشر')->assertDontSee('سحب مباشر');
        $this->actingAs($user)->get('/banks')->assertOk()->assertViewHas('balance', 1000)
            ->assertViewHas('cashboxes', fn ($accounts) => $accounts->count() === 1 && $accounts->first()->id === $bank->id);
        $this->actingAs($user)->get('/receipts?cashbox_id='.$bank->id)->assertOk()
            ->assertSee('مصرف الرافدين')->assertSee('IQ123')->assertSee('الصندوق / البنك');
        $this->actingAs($user)->put('/banks/'.$bank->id, [
            'name' => 'مصرف الرافدين الجديد', 'account_number' => 'IQ456',
            'balance' => 99999, 'is_active' => 1, 'customer_ids' => [$customer->id],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1000.0, (float) $bank->fresh()->balance);
        $this->assertSame('IQ456', $bank->fresh()->account_number);
        $this->actingAs($user)->put('/banks/'.$cash->id, ['name' => 'Invalid'])->assertNotFound();
    }

    public function test_bank_receipt_lifecycle_updates_only_the_selected_account(): void
    {
        [$company, $user, $customer, $cash] = $this->records();
        $bank = $this->bank($company);
        $data = ['receipt_date' => now()->toDateString(), 'party_type' => 'customer',
            'party_id' => $customer->id, 'cashbox_id' => $bank->id, 'amount' => 250.25];
        $this->actingAs($user)->post('/receipts', $data)->assertRedirect('/receipts');
        $receipt = Receipt::sole();
        $this->assertSame(1250.25, (float) $bank->fresh()->balance);
        $this->assertSame(100.0, (float) $cash->fresh()->balance);
        $this->actingAs($user)->get('/cashbox?cashbox_id='.$bank->id)->assertOk()
            ->assertViewHas('totalReceipts', 250.25)->assertSee($bank->name);
        $this->actingAs($user)->get('/receipts/'.$receipt->id.'/edit')->assertOk()->assertSee($bank->name);
        $this->actingAs($user)->put('/receipts/'.$receipt->id, array_replace($data, [
            'cashbox_id' => $cash->id, 'amount' => 300,
        ]))->assertRedirect('/receipts');
        $this->assertSame(1000.0, (float) $bank->fresh()->balance);
        $this->assertSame(400.0, (float) $cash->fresh()->balance);
        $this->actingAs($user)->delete('/receipts/'.$receipt->id)->assertRedirect('/receipts');
        $this->assertSame(100.0, (float) $cash->fresh()->balance);
        $this->assertSame(1000.0, (float) $bank->fresh()->balance);
    }

    public function test_deposits_withdrawals_and_linked_vouchers_are_counted_once(): void
    {
        [$company, $user, $customer, $cash] = $this->records();
        $bank = $this->bank($company);
        $bank->customers()->attach($customer);
        foreach ([
            ['type' => 'deposit', 'amount' => 80],
            ['type' => 'withdrawal', 'amount' => 30],
            ['type' => 'deposit', 'amount' => 25, 'customer_id' => $customer->id],
            ['type' => 'withdrawal', 'amount' => 10, 'customer_id' => $customer->id],
        ] as $data) {
            $this->actingAs($user)->post('/banks/'.$bank->id.'/transactions', $data)
                ->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->assertSame(1065.0, (float) $bank->fresh()->balance);
        $this->assertSame(100.0, (float) $cash->fresh()->balance);
        $this->assertSame(4, CashboxLog::where('cashbox_id', $bank->id)->distinct()->count('reference_no'));
        $this->actingAs($user)->get('/banks?cashbox_id='.$bank->id)->assertOk()
            ->assertViewHas('totalReceipts', 105)->assertViewHas('totalPayments', 40)
            ->assertDontSee('إيداع مباشر')->assertDontSee('سحب مباشر')
            ->assertViewHas('cashboxLogs', fn ($logs) => $logs->count() === 2);
    }

    public function test_invalid_and_foreign_account_operations_do_not_change_balances(): void
    {
        [$company, $user, $customer] = $this->records();
        [$other, , $foreignCustomer] = $this->records('OTHER');
        $bank = $this->bank($company);
        $foreign = $this->bank($other);
        $this->actingAs($user)->get('/banks')->assertOk()
            ->assertViewHas('cashboxes', fn ($accounts) => $accounts->count() === 1);
        $this->actingAs($user)->get('/cashbox?cashbox_id='.$foreign->id)->assertNotFound();
        $this->actingAs($user)->put('/banks/'.$foreign->id, ['name' => 'Changed'])->assertNotFound();
        $this->actingAs($user)->post('/banks/'.$foreign->id.'/transactions', [
            'type' => 'deposit', 'amount' => 10,
        ])->assertNotFound();
        foreach ([['type' => 'withdrawal', 'amount' => 1001], ['type' => 'deposit', 'amount' => 0],
            ['type' => 'invalid', 'amount' => 10]] as $data) {
            $this->actingAs($user)->from('/banks')->post('/banks/'.$bank->id.'/transactions', $data)
                ->assertRedirect('/banks')->assertSessionHasErrors();
        }
        $this->actingAs($user)->post('/banks/'.$bank->id.'/transactions', [
            'type' => 'deposit', 'amount' => 10, 'customer_id' => $foreignCustomer->id,
        ])->assertNotFound();
        $bank->update(['is_active' => false]);
        $this->actingAs($user)->post('/banks/'.$bank->id.'/transactions', [
            'type' => 'deposit', 'amount' => 10,
        ])->assertStatus(422);
        $this->actingAs($user)->post('/receipts', [
            'receipt_date' => now()->toDateString(), 'party_type' => 'customer', 'party_id' => $customer->id,
            'cashbox_id' => $bank->id, 'amount' => 10,
        ])->assertNotFound();
        $this->assertSame(1000.0, (float) $bank->fresh()->balance);
        $this->assertSame(1000.0, (float) $foreign->fresh()->balance);
        $this->assertDatabaseCount('cashbox_logs', 0);
        $this->assertDatabaseCount('receipts', 0);
        $user->update(['role' => 'data_entry']);
        $this->actingAs($user)->get('/banks')->assertForbidden();
        $this->actingAs($user)->post('/banks', ['name' => 'Forbidden'])->assertForbidden();
    }

    private function bank(Company $company): Cashbox
    {
        return Cashbox::create(['company_id' => $company->id, 'name' => 'مصرف الرافدين',
            'account_type' => 'bank', 'bank_name' => 'مصرف الرافدين', 'balance' => 1000, 'is_active' => true]);
    }

    private function records(string $suffix = ''): array
    {
        $company = Company::create(['name' => 'شركة البنوك '.$suffix, 'code' => 'BANK'.$suffix, 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth()]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'زبون تجريبي']);
        $cash = Cashbox::create(['company_id' => $company->id, 'name' => 'الصندوق الرئيسي',
            'balance' => 100, 'is_active' => true]);
        return [$company, $user, $customer, $cash];
    }
}
