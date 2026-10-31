<?php

namespace Tests\Feature;

use App\Models\Cashbox;
use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\Customer;
use App\Models\ExternalInvoice;
use App\Models\PartyDebtTransaction;
use App\Models\Receipt;
use App\Models\User;
use App\Services\CustomerBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KudiaCustomerSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_and_sippar_lists_invoice_selectors_statements_and_edits_are_separate(): void
    {
        [$user, $general, $factory] = $this->records();
        $this->actingAs($user);
        $this->get('/customers')->assertOk()->assertSee($general->name)->assertDontSee($factory->name);
        $this->get('/kudia/customers')->assertOk()->assertSee($factory->name)->assertDontSee($general->name);
        $this->get('/kudia/invoices/create')->assertOk()->assertSee($factory->name)->assertDontSee($general->name);
        $this->get('/customers/'.$factory->id)->assertNotFound();
        $this->get('/customers/'.$factory->id.'/pdf')->assertNotFound();
        $this->get('/customers/'.$factory->id.'/excel')->assertNotFound();
        $this->get('/kudia/customers/'.$general->id)->assertNotFound();
        $this->get('/kudia/customers/'.$general->id.'/pdf')->assertNotFound();
        $this->put('/customers/'.$factory->id, ['name' => 'Wrong group'])->assertNotFound();
        $this->put('/kudia/customers/'.$general->id, ['name' => 'Wrong group'])->assertNotFound();
        $this->get('/kudia/customers/'.$factory->id.'/edit')->assertOk();
        $this->post('/kudia/invoices', $this->invoiceData($general))->assertSessionHasErrors('customer_id');
        $this->assertDatabaseCount('external_invoices', 0);
        $this->post('/kudia/invoices', $this->invoiceData($factory))->assertRedirect();
        $this->get('/customers/'.$general->id)->assertOk()->assertViewHas('balance', 0.0);
        $this->get('/kudia/customers/'.$factory->id)->assertOk()->assertViewHas('balance', 20.0);
    }

    public function test_customer_creation_assigns_the_group_from_the_route_and_ignores_supplied_group(): void
    {
        [$user] = $this->records();
        $this->actingAs($user)->post('/kudia/customers', ['name' => 'New factory client', 'account_group' => 'general'])
            ->assertRedirect('/kudia/customers');
        $factory = Customer::where('name', 'New factory client')->sole();
        $this->assertSame(Customer::GROUP_KUDIA, $factory->account_group);
        $this->post('/customers', ['name' => 'New Sippar client', 'account_group' => 'kudia'])->assertRedirect('/customers');
        $general = Customer::where('name', 'New Sippar client')->sole();
        $this->assertSame(Customer::GROUP_GENERAL, $general->account_group);
        $this->put('/kudia/customers/'.$factory->id, ['name' => 'Updated factory client', 'account_group' => 'general'])
            ->assertRedirect('/kudia/customers');
        $this->assertSame(Customer::GROUP_KUDIA, $factory->fresh()->account_group);
    }

    public function test_vouchers_and_cancellation_affect_only_the_selected_factory_customer(): void
    {
        [$user, $general, $factory, $bank] = $this->records();
        $this->actingAs($user);
        $voucher = ['party_type' => 'customer', 'cashbox_id' => $bank->id, 'amount' => 50, 'receipt_date' => '2026-10-28'];
        $this->post('/receipts', $voucher + ['party_id' => $general->id])->assertRedirect('/receipts');
        $this->post('/receipts', $voucher + ['party_id' => $factory->id])->assertNotFound();
        $this->post('/kudia/receipts', $voucher + ['party_id' => $general->id])->assertNotFound();
        $this->post('/kudia/receipts', array_replace($voucher, ['party_id' => $factory->id, 'amount' => 13]))->assertRedirect('/kudia/receipts');
        $this->post('/kudia/payments', ['party_type' => 'customer', 'party_id' => $factory->id,
            'cashbox_id' => $bank->id, 'amount' => 2, 'payment_date' => '2026-10-28'])->assertRedirect('/kudia/payments');
        $this->assertSame(-50.0, $this->balance($general));
        $this->assertSame(-11.0, $this->balance($factory));
        $this->assertSame(1061.0, (float) $bank->fresh()->balance);
        $this->get('/receipts')->assertOk()->assertViewHas('customers', fn ($rows) => $rows->modelKeys() === [$general->id])
            ->assertViewHas('receipts', fn ($rows) => $rows->count() === 1 && $rows->first()->customer_id === $general->id);
        $this->get('/kudia/receipts')->assertOk()->assertViewHas('customers', fn ($rows) => $rows->modelKeys() === [$factory->id])
            ->assertViewHas('receipts', fn ($rows) => $rows->count() === 1 && $rows->first()->customer_id === $factory->id);
        $this->get('/payments')->assertOk()->assertViewHas('payments', fn ($rows) => $rows->isEmpty());
        $this->get('/kudia/payments')->assertOk()->assertViewHas('payments', fn ($rows) => $rows->count() === 1);
        $receipt = Receipt::where('customer_id', $factory->id)->sole();
        $this->get('/receipts/'.$receipt->id.'/edit')->assertNotFound();
        $this->get('/receipts/'.$receipt->id.'/print')->assertNotFound();
        $this->get('/kudia/receipts/'.$receipt->id.'/edit')->assertOk()->assertSee('/kudia/receipts/'.$receipt->id, false);
        $this->delete('/receipts/'.$receipt->id)->assertNotFound();
        $this->delete('/kudia/receipts/'.$receipt->id)->assertRedirect('/kudia/receipts');
        $this->assertSame(2.0, $this->balance($factory));
        $this->assertSame(-50.0, $this->balance($general));
        $this->assertSame(1048.0, (float) $bank->fresh()->balance);
    }

    public function test_debt_movements_cannot_cross_customer_groups(): void
    {
        [$user, $general, $factory] = $this->records();
        $data = ['party_type' => 'customer', 'type' => 'borrowing', 'transaction_date' => '2026-10-28', 'amount' => 100];
        $this->actingAs($user)->post('/party-debt-transactions', $data + ['party_id' => $factory->id])->assertNotFound();
        $this->post('/kudia/party-debt-transactions', $data + ['party_id' => $general->id])->assertNotFound();
        $this->post('/kudia/party-debt-transactions', $data + ['party_id' => $factory->id])->assertRedirect('/kudia/customers/'.$factory->id);
        $transaction = PartyDebtTransaction::sole();
        $this->get('/party-debt-transactions/'.$transaction->id.'/edit')->assertNotFound();
        $this->get('/kudia/party-debt-transactions/'.$transaction->id.'/edit')->assertOk();
        $this->put('/kudia/party-debt-transactions/'.$transaction->id, array_replace($data, ['amount' => 75]))->assertRedirect();
        $this->assertSame(75.0, $this->balance($factory));
        $this->delete('/kudia/party-debt-transactions/'.$transaction->id)->assertRedirect();
        $this->assertSame(0.0, $this->balance($factory));
        $this->assertSame(0.0, $this->balance($general));
    }

    public function test_legacy_factory_invoices_are_preserved_without_reclassifying_general_customers(): void
    {
        [$user, $general] = $this->records();
        $invoice = ExternalInvoice::create(['company_id' => $general->company_id, 'customer_id' => $general->id,
            'external_invoice_id' => 'legacy-kudia', 'external_customer_id' => $general->integration_id,
            'invoice_no' => 'KUD-LEGACY', 'invoice_date' => '2026-10-24', 'provider' => 'kudia',
            'currency' => 'IQD', 'amount' => 2000, 'subtotal' => 2000, 'status' => 'active', 'payload' => ['items' => []]]);
        $this->actingAs($user)->get('/kudia')->assertOk()->assertViewHas('legacyInvoiceCount', 1);
        $this->get('/kudia/invoices/'.$invoice->id)->assertOk()->assertSee('/customers/'.$general->id, false);
        $this->get('/kudia/invoices/'.$invoice->id.'/edit')->assertOk()->assertSee('هذه فاتورة سابقة مرتبطة بزبون سيبار');
        $this->assertSame(Customer::GROUP_GENERAL, $general->fresh()->account_group);
        $this->assertSame(2000.0, $this->balance($general));
        $this->assertSame($general->id, $invoice->fresh()->customer_id);
    }

    private function balance(Customer $customer): float
    {
        return app(CustomerBalanceService::class)->calculate($customer)['currentBalance'];
    }

    private function records(): array
    {
        $company = Company::create(['name' => 'Sippar', 'code' => 'SIPPAR', 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addYear()]);
        CompanyFeature::create(['company_id' => $company->id, 'feature_key' => 'kudia_factory', 'enabled' => true]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $general = Customer::create(['company_id' => $company->id, 'name' => 'Sippar only customer']);
        $factory = Customer::create(['company_id' => $company->id, 'name' => 'Factory only customer', 'account_group' => Customer::GROUP_KUDIA]);
        $bank = Cashbox::create(['company_id' => $company->id, 'name' => 'Shared bank', 'balance' => 1000, 'is_active' => true]);

        return [$user, $general, $factory, $bank];
    }

    private function invoiceData(Customer $customer): array
    {
        return ['customer_id' => $customer->id, 'invoice_date' => '2026-10-24', 'tax_rate' => 0,
            'items' => [['description' => 'Factory item', 'unit_price' => 10, 'quantity' => 2, 'unit' => 'cartons']]];
    }
}
