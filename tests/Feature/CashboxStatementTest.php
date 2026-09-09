<?php

namespace Tests\Feature;

use App\Models\{Cashbox, Company, Customer, Receipt, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CashboxStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_statement_carries_opening_balance_and_filters_dates_inclusively(): void
    {
        [$company, $user, $customer, $bank] = $this->records();
        $bank->customers()->attach($customer);
        $this->actingAs($user);
        $this->travelTo(Carbon::parse('2026-09-01 09:00:00'));
        $this->post('/banks/'.$bank->id.'/transactions', ['type' => 'deposit', 'amount' => 100.25])->assertRedirect();
        $this->travelTo(Carbon::parse('2026-09-02 09:00:00'));
        $this->post('/receipts', $this->receiptData($customer, $bank, 250.50))->assertRedirect('/receipts');
        $this->travelTo(Carbon::parse('2026-09-03 09:00:00'));
        $this->post('/banks/'.$bank->id.'/transactions', ['type' => 'withdrawal', 'amount' => 50.25])->assertRedirect();
        $this->travelTo(Carbon::parse('2026-09-03 10:00:00'));
        $this->post('/banks/'.$bank->id.'/transactions', ['type' => 'deposit', 'amount' => 25, 'customer_id' => $customer->id])->assertRedirect();
        $this->travelTo(Carbon::parse('2026-09-04 09:00:00'));
        $this->post('/banks/'.$bank->id.'/transactions', ['type' => 'withdrawal', 'amount' => 20, 'customer_id' => $customer->id])->assertRedirect();
        $this->get('/banks/'.$bank->id.'/statement')->assertOk()
            ->assertViewHas('openingBalance', 1000)->assertViewHas('closingBalance', 1305.50)
            ->assertViewHas('totalIncoming', 375.75)->assertViewHas('totalOutgoing', 70.25)
            ->assertViewHas('movements', fn ($rows) => $rows->count() === 5)
            ->assertSee('كشف حساب')->assertDontSee('إيداع مباشر')->assertDontSee('سحب مباشر');
        $range = '/banks/'.$bank->id.'/statement?from=2026-09-02&to=2026-09-03';
        $this->get($range)->assertOk()->assertViewHas('openingBalance', 1100.25)
            ->assertViewHas('closingBalance', 1325.50)->assertViewHas('totalIncoming', 275.50)
            ->assertViewHas('totalOutgoing', 50.25)->assertViewHas('movements', function ($rows) {
                $this->assertEquals([1350.75, 1300.50, 1325.50], $rows->pluck('balance')->all());
                return $rows->count() === 3;
            });
        $this->get('/banks/'.$bank->id.'/statement/print?from=2026-09-02&to=2026-09-03')
            ->assertOk()->assertViewHas('openingBalance', 1100.25)->assertViewHas('closingBalance', 1325.50)
            ->assertSee('رصيد أول المدة')->assertSee('1,325.50');
        $this->get('/banks/'.$bank->id.'/statement?to=2026-09-01')->assertOk()
            ->assertViewHas('openingBalance', 1000)->assertViewHas('closingBalance', 1100.25);
        $this->get('/banks/'.$bank->id.'/statement?from=2026-09-04')->assertOk()
            ->assertViewHas('openingBalance', 1325.50)->assertViewHas('closingBalance', 1305.50);
        $this->get('/banks/'.$bank->id.'/statement?from=2026-09-10&to=2026-09-11')->assertOk()
            ->assertViewHas('openingBalance', 1305.50)->assertViewHas('closingBalance', 1305.50)
            ->assertViewHas('totalIncoming', 0)->assertViewHas('totalOutgoing', 0)
            ->assertSee('لا توجد حركات ضمن الفترة المختارة.');
    }

    public function test_edited_moved_and_deleted_receipts_are_not_counted_twice(): void
    {
        [, $user, $customer, $bank, $cash] = $this->records();
        $this->actingAs($user);
        $data = $this->receiptData($customer, $bank, 100);
        $this->post('/receipts', $data)->assertRedirect('/receipts');
        $receipt = Receipt::sole();
        $this->put('/receipts/'.$receipt->id, array_replace($data, ['amount' => 150]))->assertRedirect('/receipts');
        $this->get('/banks/'.$bank->id.'/statement')->assertOk()->assertViewHas('openingBalance', 1000)
            ->assertViewHas('closingBalance', 1150)->assertViewHas('totalIncoming', 150)
            ->assertViewHas('movements', fn ($rows) => $rows->count() === 1);
        $this->put('/receipts/'.$receipt->id, array_replace($data, ['cashbox_id' => $cash->id, 'amount' => 150]))->assertRedirect('/receipts');
        $this->get('/banks/'.$bank->id.'/statement')->assertOk()->assertViewHas('openingBalance', 1000)
            ->assertViewHas('closingBalance', 1000)->assertViewHas('movements', fn ($rows) => $rows->isEmpty());
        $this->get('/cashbox/'.$cash->id.'/statement')->assertOk()->assertViewHas('openingBalance', 200)
            ->assertViewHas('closingBalance', 350);
        $this->delete('/receipts/'.$receipt->id)->assertRedirect('/receipts');
        $this->get('/cashbox/'.$cash->id.'/statement')->assertOk()->assertViewHas('openingBalance', 200)
            ->assertViewHas('closingBalance', 200)->assertViewHas('movements', fn ($rows) => $rows->isEmpty());
    }

    public function test_statement_and_print_are_company_scoped_and_validate_dates(): void
    {
        [, $user, , $bank, $cash] = $this->records();
        [, , , $otherBank] = $this->records('OTHER');
        $this->actingAs($user);
        foreach (['/statement', '/statement/print'] as $suffix) {
            $this->get('/banks/'.$otherBank->id.$suffix)->assertNotFound();
            $this->get('/cashbox/'.$otherBank->id.$suffix)->assertNotFound();
            $this->get('/banks/'.$cash->id.$suffix)->assertNotFound();
        }
        $this->from('/banks/'.$bank->id.'/statement')->get('/banks/'.$bank->id.'/statement?from=2026-09-04&to=2026-09-01')
            ->assertRedirect('/banks/'.$bank->id.'/statement')->assertSessionHasErrors('to');
        $this->from('/banks/'.$bank->id.'/statement')->get('/banks/'.$bank->id.'/statement/print?from=invalid')
            ->assertRedirect('/banks/'.$bank->id.'/statement')->assertSessionHasErrors('from');
        $user->update(['role' => 'accountant']);
        $bank->update(['is_active' => false]);
        $this->actingAs($user)->get('/banks/'.$bank->id.'/statement')->assertOk();
        $user->update(['role' => 'data_entry']);
        $this->actingAs($user)->get('/banks/'.$bank->id.'/statement')->assertForbidden();
        $this->get('/banks/'.$bank->id.'/statement/print')->assertForbidden();
    }

    private function receiptData(Customer $customer, Cashbox $bank, float $amount): array
    {
        return ['receipt_date' => now()->toDateString(), 'party_type' => 'customer',
            'party_id' => $customer->id, 'cashbox_id' => $bank->id, 'amount' => $amount];
    }

    private function records(string $suffix = ''): array
    {
        $company = Company::create(['name' => 'Statement '.$suffix, 'code' => 'ST'.$suffix, 'status' => 'active',
            'subscription_start' => '2026-01-01', 'subscription_end' => '2027-01-01']);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'زبون']);
        $bank = Cashbox::create(['company_id' => $company->id, 'name' => 'مصرف الرافدين', 'account_type' => 'bank',
            'bank_name' => 'مصرف الرافدين', 'balance' => 1000, 'is_active' => true]);
        $cash = Cashbox::create(['company_id' => $company->id, 'name' => 'الصندوق', 'balance' => 200, 'is_active' => true]);
        return [$company, $user, $customer, $bank, $cash];
    }
}
