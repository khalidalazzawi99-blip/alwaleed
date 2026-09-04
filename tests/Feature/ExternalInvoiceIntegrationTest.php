<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\Cashbox;
use App\Models\Customer;
use App\Models\ExternalInvoice;
use App\Models\ExternalInvoiceIntegration;
use App\Models\Receipt;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Services\CustomerBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExternalInvoiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_invoice_is_received_and_company_comes_from_api_key(): void
    {
        $company = $this->company();
        $customer = $this->customer($company);
        [$plain] = $this->credential($company);

        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload(['customer_id' => $customer->integration_id]))
            ->assertCreated()->assertJsonPath('data.customer_id', $customer->integration_id);

        $this->assertDatabaseHas('external_invoices', [
            'company_id' => $company->id, 'customer_id' => $customer->id,
            'external_invoice_id' => '4812', 'invoice_no' => 'INV-2026-4812', 'amount' => 1250000,
        ]);
    }

    public function test_invalid_api_key_is_rejected(): void
    {
        $this->withToken('invalid')->postJson('/api/v1/external-invoices', $this->payload())->assertUnauthorized();
    }

    public function test_bearer_token_with_accidental_surrounding_whitespace_is_accepted(): void
    {
        $company = $this->company();
        [$plain] = $this->credential($company);

        $this->withHeader('Authorization', 'Bearer '.$plain."\t")
            ->getJson('/api/v1/external-customers')
            ->assertOk();
    }

    public function test_activation_command_restores_the_company_integration_and_all_scopes(): void
    {
        $company = $this->company('Sippar');
        $company->update(['status' => 'inactive']);
        ExternalInvoiceIntegration::create([
            'company_id' => $company->id,
            'provider' => 'default',
            'enabled' => false,
        ]);
        $plain = 'aw_live_'.str_repeat('A', 48);

        $this->artisan('company-api-token:activate', ['company' => $company->name])
            ->expectsQuestion('Paste the API token', $plain)
            ->assertSuccessful();

        $token = CompanyApiToken::where('token_hash', hash('sha256', $plain))->firstOrFail();
        $this->assertSame($company->id, $token->company_id);
        $this->assertNull($token->revoked_at);
        $this->assertEqualsCanonicalizing(
            ['invoices:read', 'invoices:write', 'balances:read', 'customers:read', 'banks:read'],
            $token->scopes
        );
        $this->assertSame('active', $company->fresh()->status);
        $this->assertTrue(ExternalInvoiceIntegration::where('company_id', $company->id)->value('enabled'));
    }

    public function test_duplicate_invoice_is_updated_not_duplicated(): void
    {
        $company = $this->company(); $customer = $this->customer($company); [$plain] = $this->credential($company);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload(['customer_id' => $customer->integration_id]))->assertCreated();
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload(['customer_id' => $customer->integration_id, 'amount' => 1500000]))->assertOk();
        $this->assertSame(1, ExternalInvoice::where('company_id', $company->id)->where('external_invoice_id', '4812')->count());
        $this->assertDatabaseHas('external_invoices', ['company_id' => $company->id, 'external_invoice_id' => '4812', 'amount' => 1500000]);
    }

    public function test_customer_and_bank_feeds_are_scoped_paginated_and_include_balances(): void
    {
        $company = $this->company();
        Setting::create(['company_id' => $company->id, 'company_name' => 'Test', 'currency' => 'USD']);
        $customer = $this->customer($company);
        $this->invoice($company, $customer, '1', 1000);
        $this->receipt($company, $customer, 250);
        Cashbox::create(['company_id' => $company->id, 'name' => 'Main Bank', 'balance' => 50000, 'is_active' => true]);
        [$plain] = $this->credential($company);

        $this->withToken($plain)->getJson('/api/v1/external-customers?per_page=25')
            ->assertOk()->assertJsonPath('data.0.customer_id', $customer->integration_id)
            ->assertJsonPath('data.0.balance', 750)->assertJsonPath('data.0.currency', 'USD')
            ->assertJsonPath('meta.total', 1)->assertJsonStructure(['data' => [['updated_at']], 'meta']);
        $this->withToken($plain)->getJson('/api/v1/external-banks')
            ->assertOk()->assertJsonPath('data.0.name', 'Main Bank')
            ->assertJsonPath('data.0.balance', 50000)->assertJsonPath('meta.total', 1)
            ->assertJsonStructure(['data' => [['bank_id', 'updated_at']], 'meta']);
    }

    public function test_cancelled_invoice_no_longer_affects_customer_balance(): void
    {
        $company = $this->company();
        Setting::create(['company_id' => $company->id, 'company_name' => 'Test', 'currency' => 'IQD']);
        $customer = $this->customer($company);
        [$plain] = $this->credential($company);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload(['customer_id' => $customer->integration_id]))->assertCreated();
        $this->assertSame(1250000.0, app(CustomerBalanceService::class)->calculate($customer)['outstandingBalance']);

        $this->withToken($plain)->postJson('/api/v1/external-invoices/4812/cancel')
            ->assertOk()->assertJsonPath('data.status', 'cancelled');
        $this->withToken($plain)->postJson('/api/v1/external-invoices/4812/cancel')->assertOk();
        $this->assertSame(0.0, app(CustomerBalanceService::class)->calculate($customer)['outstandingBalance']);
    }

    public function test_unknown_external_customer_is_rejected_without_invoice(): void
    {
        $company = $this->company(); [$plain] = $this->credential($company);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload(['customer_id' => 'C-01KZZZZZZZZZZZZZZZZZZZZZZZ']))->assertNotFound();
        $this->assertDatabaseCount('external_invoices', 0);
    }

    public function test_company_a_key_cannot_create_invoice_for_company_b_customer(): void
    {
        $companyA = $this->company('A'); $companyB = $this->company('B');
        $customerB = $this->customer($companyB); [$plainA] = $this->credential($companyA);
        $this->withToken($plainA)->postJson('/api/v1/external-invoices', $this->payload(['customer_id' => $customerB->integration_id]))->assertNotFound();
        $this->assertDatabaseCount('external_invoices', 0);
    }

    public function test_customer_invoice_receipt_and_outstanding_totals_are_correct(): void
    {
        $company = $this->company(); $customer = $this->customer($company);
        $this->invoice($company, $customer, '1', 1000000); $this->invoice($company, $customer, '2', 750000); $this->invoice($company, $customer, '3', 250000);
        $receipt = $this->receipt($company, $customer, 500000);
        $balance = app(CustomerBalanceService::class)->calculate($customer);
        $this->assertSame(2000000.0, $balance['totalInvoices']);
        $this->assertSame(500000.0, $balance['totalReceipts']);
        $this->assertSame(1500000.0, $balance['outstandingBalance']);

        $receipt->update(['amount' => 750000]);
        $this->assertSame(1250000.0, app(CustomerBalanceService::class)->calculate($customer)['outstandingBalance']);
        $receipt->delete();
        $this->assertSame(2000000.0, app(CustomerBalanceService::class)->calculate($customer)['outstandingBalance']);
    }

    public function test_creating_receipt_reduces_balance_without_editing_invoice(): void
    {
        $company = $this->company(); $customer = $this->customer($company);
        $invoice = $this->invoice($company, $customer, '1', 3000000)->fresh();
        $fields = ['company_id', 'customer_id', 'external_invoice_id', 'invoice_no', 'invoice_date', 'amount', 'updated_at'];
        $original = collect($fields)->mapWithKeys(fn ($field) => [$field => $invoice->getRawOriginal($field)])->all();
        $this->receipt($company, $customer, 750000);
        $this->assertSame(2250000.0, app(CustomerBalanceService::class)->calculate($customer)['outstandingBalance']);
        $freshInvoice = $invoice->fresh();
        $this->assertSame($original, collect($fields)->mapWithKeys(fn ($field) => [$field => $freshInvoice->getRawOriginal($field)])->all());
    }

    public function test_disabled_or_revoked_credential_cannot_submit_invoice(): void
    {
        $company = $this->company(); $customer = $this->customer($company); [$plain, $token] = $this->credential($company, false);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload(['customer_id' => $customer->integration_id]))->assertForbidden();
        ExternalInvoiceIntegration::where('company_id', $company->id)->update(['enabled' => true]);
        $token->update(['revoked_at' => now()]);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload(['customer_id' => $customer->integration_id]))->assertUnauthorized();
    }

    public function test_customer_page_is_company_scoped_and_displays_financial_totals(): void
    {
        $company = $this->company(); $other = $this->company('OTHER');
        $customer = $this->customer($company); $otherCustomer = $this->customer($other);
        $this->invoice($company, $customer, 'A-1', 1000); $this->invoice($other, $otherCustomer, 'B-1', 9000000);
        $this->receipt($company, $customer, 250);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $this->actingAs($user)->get('/customers/'.$customer->id)->assertOk()
            ->assertSee('1,000.00')->assertSee('250.00')->assertSee('750.00')->assertDontSee('9,000,000.00');
        $this->actingAs($user)->get('/customers/'.$otherCustomer->id)->assertForbidden();
    }

    public function test_customer_with_receipts_only_has_a_positive_balance(): void
    {
        $company = $this->company();
        $customer = $this->customer($company);
        $this->receipt($company, $customer, 500000);
        Payment::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'payment_no' => 'PAY-'.Str::random(8),
            'payment_date' => now(),
            'amount' => 125000,
        ]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

        $this->actingAs($user)->get('/customers/'.$customer->id)
            ->assertOk()
            ->assertViewHas('balance', 375000.0)
            ->assertSee('375,000.00');
    }

    public function test_invoice_appears_in_customer_movement_history_in_date_order(): void
    {
        $company = $this->company();
        $customer = $this->customer($company);
        $this->invoice($company, $customer, 'INV-MOVEMENT', 1000);
        $this->receipt($company, $customer, 250);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

        $this->actingAs($user)->get('/customers/'.$customer->id)
            ->assertOk()
            ->assertViewHas('movements', function ($movements) {
                return $movements->count() === 2
                    && $movements[0]->type === 'فاتورة'
                    && $movements[0]->balance === 1000.0
                    && $movements[1]->type === 'قبض'
                    && $movements[1]->balance === 750.0;
            });
    }

    public function test_balance_endpoints_include_payments_and_match_customer_current_balance(): void
    {
        $company = $this->company();
        $customer = $this->customer($company);
        [$plain] = $this->credential($company);
        Setting::create(['company_id' => $company->id, 'company_name' => 'Test', 'currency' => 'USD']);
        $this->invoice($company, $customer, 'CURRENT-BALANCE', 2775);
        $this->receipt($company, $customer, 4500);
        $this->payment($company, $customer, 3525);

        $expected = [
            'customer_id' => $customer->integration_id,
            'total_invoices' => 2775,
            'total_receipts' => 4500,
            'total_payments' => 3525,
            'current_balance' => 1800,
            'balance' => 1800,
            'currency' => 'USD',
        ];

        $this->withToken($plain)
            ->getJson('/api/v1/customers/'.$customer->integration_id.'/balance')
            ->assertOk()
            ->assertJson(['data' => $expected]);

        $this->withToken($plain)
            ->getJson('/api/v1/external-customers')
            ->assertOk()
            ->assertJson(['data' => [$expected]]);
    }

    private function company(string $suffix = ''): Company
    {
        return Company::create(['name' => 'Company '.$suffix, 'code' => 'C'.Str::upper(Str::random(8)), 'status' => 'active', 'subscription_start' => now(), 'subscription_end' => now()->addYear()]);
    }

    public function test_customer_integration_id_is_generated_and_immutable(): void
    {
        $customer = $this->customer($this->company());
        $original = $customer->integration_id;
        $this->assertMatchesRegularExpression('/^C-[0-9A-HJKMNP-TV-Z]{26}$/', $original);

        $customer->update(['name' => 'Renamed Customer']);
        $customer->forceFill(['integration_id' => 'C-CHANGED'])->save();
        $this->assertSame($original, $customer->fresh()->integration_id);
    }

    public function test_balance_and_invoice_lists_use_public_customer_id_and_pagination(): void
    {
        $company = $this->company(); $customer = $this->customer($company); [$plain] = $this->credential($company);
        Setting::create(['company_id' => $company->id, 'company_name' => 'Test', 'currency' => 'IQD']);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload(['customer_id' => $customer->integration_id]))->assertCreated();
        $this->receipt($company, $customer, 250000);

        $this->withToken($plain)->getJson('/api/v1/customers/'.$customer->integration_id.'/balance')
            ->assertOk()->assertJsonPath('data.customer_id', $customer->integration_id)
            ->assertJsonPath('data.balance', 1000000);
        $this->invoice($company, $customer, 'SECOND', 500);
        $this->withToken($plain)->getJson('/api/v1/external-invoices?per_page=1&page=2')
            ->assertOk()->assertJsonPath('data.0.customer_id', $customer->integration_id)
            ->assertJsonPath('meta.current_page', 2)->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_customer_and_bank_incremental_sync_and_pagination_work(): void
    {
        $company = $this->company(); [$plain] = $this->credential($company);
        $customer = $this->customer($company);
        $bank = Cashbox::create(['company_id' => $company->id, 'name' => 'Main', 'balance' => 1, 'is_active' => true]);
        $this->customer($company);
        Cashbox::create(['company_id' => $company->id, 'name' => 'Second', 'balance' => 2, 'is_active' => true]);
        $future = now()->addMinute()->toIso8601String();

        $this->withToken($plain)->getJson('/api/v1/external-customers?per_page=1&page=1')
            ->assertJsonPath('data.0.customer_id', $customer->integration_id)->assertJsonPath('meta.last_page', 2);
        $this->withToken($plain)->getJson('/api/v1/external-banks?per_page=1&page=1')
            ->assertJsonPath('data.0.bank_id', $bank->integration_id)->assertJsonPath('meta.last_page', 2);
        $this->withToken($plain)->getJson('/api/v1/external-customers?updated_since='.urlencode($future))->assertJsonCount(0, 'data');
        $this->withToken($plain)->getJson('/api/v1/external-banks?updated_since='.urlencode($future))->assertJsonCount(0, 'data');
    }

    public function test_legacy_external_customer_id_field_is_temporarily_accepted(): void
    {
        $company = $this->company(); $customer = $this->customer($company); [$plain] = $this->credential($company);
        $payload = $this->payload(['external_customer_id' => $customer->integration_id]);
        unset($payload['customer_id']);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $payload)
            ->assertCreated()->assertJsonPath('data.customer_id', $customer->integration_id);
    }

    private function customer(Company $company): Customer
    {
        return Customer::create(['company_id' => $company->id, 'name' => 'Customer '.$company->id]);
    }

    private function credential(Company $company, bool $enabled = true): array
    {
        ExternalInvoiceIntegration::create(['company_id' => $company->id, 'provider' => 'default', 'enabled' => $enabled]);
        $plain = 'aw_test_'.Str::random(40);
        $token = CompanyApiToken::create(['company_id' => $company->id, 'name' => 'Test', 'token_hash' => hash('sha256', $plain), 'token_prefix' => substr($plain, 0, 14), 'scopes' => ['invoices:read', 'invoices:write', 'customers:read', 'banks:read', 'balances:read']]);
        return [$plain, $token];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['external_invoice_id' => '4812', 'invoice_no' => 'INV-2026-4812', 'invoice_name' => 'Sales Invoice INV-2026-4812', 'order_no' => 'ORD-20260819-001', 'invoice_date' => '2026-08-25', 'currency' => 'IQD', 'amount' => 1250000, 'status' => 'active'], $overrides);
    }

    private function invoice(Company $company, Customer $customer, string $externalId, float $amount): ExternalInvoice
    {
        return ExternalInvoice::create(['company_id' => $company->id, 'customer_id' => $customer->id, 'external_invoice_id' => $externalId, 'external_customer_id' => $customer->integration_id, 'invoice_no' => 'INV-'.$externalId, 'invoice_date' => now(), 'amount' => $amount]);
    }

    private function payment(Company $company, Customer $customer, float $amount): Payment
    {
        return Payment::create(['company_id' => $company->id, 'customer_id' => $customer->id, 'payment_no' => 'PAY-'.Str::random(8), 'payment_date' => now(), 'amount' => $amount]);
    }

    private function receipt(Company $company, Customer $customer, float $amount): Receipt
    {
        return Receipt::create(['company_id' => $company->id, 'customer_id' => $customer->id, 'receipt_no' => 'RCP-'.Str::random(8), 'receipt_date' => now(), 'amount' => $amount]);
    }
}
