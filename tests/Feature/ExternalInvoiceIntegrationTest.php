<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\Customer;
use App\Models\ExternalInvoice;
use App\Models\ExternalInvoiceIntegration;
use App\Models\Receipt;
use App\Models\Payment;
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
        $customer = $this->customer($company, 'C-155');
        [$plain] = $this->credential($company);

        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload())
            ->assertCreated()->assertJsonPath('data.customer_id', $customer->id);

        $this->assertDatabaseHas('external_invoices', [
            'company_id' => $company->id, 'customer_id' => $customer->id,
            'external_invoice_id' => '4812', 'invoice_no' => 'INV-2026-4812', 'amount' => 1250000,
        ]);
    }

    public function test_invalid_api_key_is_rejected(): void
    {
        $this->withToken('invalid')->postJson('/api/v1/external-invoices', $this->payload())->assertUnauthorized();
    }

    public function test_duplicate_invoice_is_updated_not_duplicated(): void
    {
        $company = $this->company(); $this->customer($company, 'C-155'); [$plain] = $this->credential($company);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload())->assertCreated();
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload(['amount' => 1500000]))->assertOk();
        $this->assertSame(1, ExternalInvoice::where('company_id', $company->id)->where('external_invoice_id', '4812')->count());
        $this->assertDatabaseHas('external_invoices', ['company_id' => $company->id, 'external_invoice_id' => '4812', 'amount' => 1500000]);
    }

    public function test_unknown_external_customer_is_rejected_without_invoice(): void
    {
        $company = $this->company(); [$plain] = $this->credential($company);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload())->assertNotFound();
        $this->assertDatabaseCount('external_invoices', 0);
    }

    public function test_company_a_key_cannot_create_invoice_for_company_b_customer(): void
    {
        $companyA = $this->company('A'); $companyB = $this->company('B');
        $this->customer($companyB, 'C-155'); [$plainA] = $this->credential($companyA);
        $this->withToken($plainA)->postJson('/api/v1/external-invoices', $this->payload())->assertNotFound();
        $this->assertDatabaseCount('external_invoices', 0);
    }

    public function test_customer_invoice_receipt_and_outstanding_totals_are_correct(): void
    {
        $company = $this->company(); $customer = $this->customer($company, 'C-155');
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
        $company = $this->company(); $customer = $this->customer($company, 'C-155');
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
        $company = $this->company(); $this->customer($company, 'C-155'); [$plain, $token] = $this->credential($company, false);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload())->assertForbidden();
        ExternalInvoiceIntegration::where('company_id', $company->id)->update(['enabled' => true]);
        $token->update(['revoked_at' => now()]);
        $this->withToken($plain)->postJson('/api/v1/external-invoices', $this->payload())->assertUnauthorized();
    }

    public function test_customer_page_is_company_scoped_and_displays_financial_totals(): void
    {
        $company = $this->company(); $other = $this->company('OTHER');
        $customer = $this->customer($company, 'C-155'); $otherCustomer = $this->customer($other, 'C-155');
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
        $customer = $this->customer($company, 'C-155');
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
        $customer = $this->customer($company, 'C-155');
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

    private function company(string $suffix = ''): Company
    {
        return Company::create(['name' => 'Company '.$suffix, 'code' => 'C'.Str::upper(Str::random(8)), 'status' => 'active', 'subscription_start' => now(), 'subscription_end' => now()->addYear()]);
    }

    private function customer(Company $company, string $externalId): Customer
    {
        return Customer::create(['company_id' => $company->id, 'external_customer_id' => $externalId, 'name' => 'Customer '.$company->id]);
    }

    private function credential(Company $company, bool $enabled = true): array
    {
        ExternalInvoiceIntegration::create(['company_id' => $company->id, 'provider' => 'default', 'enabled' => $enabled]);
        $plain = 'aw_test_'.Str::random(40);
        $token = CompanyApiToken::create(['company_id' => $company->id, 'name' => 'Test', 'token_hash' => hash('sha256', $plain), 'token_prefix' => substr($plain, 0, 14), 'scopes' => ['invoices:write']]);
        return [$plain, $token];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['external_invoice_id' => '4812', 'invoice_no' => 'INV-2026-4812', 'external_customer_id' => 'C-155', 'invoice_date' => '2026-08-19', 'amount' => 1250000], $overrides);
    }

    private function invoice(Company $company, Customer $customer, string $externalId, float $amount): ExternalInvoice
    {
        return ExternalInvoice::create(['company_id' => $company->id, 'customer_id' => $customer->id, 'external_invoice_id' => $externalId, 'external_customer_id' => $customer->external_customer_id, 'invoice_no' => 'INV-'.$externalId, 'invoice_date' => now(), 'amount' => $amount]);
    }

    private function receipt(Company $company, Customer $customer, float $amount): Receipt
    {
        return Receipt::create(['company_id' => $company->id, 'customer_id' => $customer->id, 'receipt_no' => 'RCP-'.Str::random(8), 'receipt_date' => now(), 'amount' => $amount]);
    }
}
