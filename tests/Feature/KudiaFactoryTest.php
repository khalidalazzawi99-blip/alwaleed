<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\Customer;
use App\Models\ExternalInvoice;
use App\Models\Receipt;
use App\Models\Setting;
use App\Models\User;
use App\Services\CustomerBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KudiaFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sippar_activation_uses_the_existing_company_customers_and_accounts(): void
    {
        [$company, $user, $customer] = $this->records();
        $company->update(['code' => 'SIPPAR', 'name' => 'أضواء سيبار']);
        $other = Company::create(['name' => 'Other company', 'code' => 'UNRELATED', 'status' => 'active']);
        $companyCount = Company::count();
        $customerCount = Customer::count();
        $userCount = User::count();
        $migration = require database_path('migrations/2026_10_28_180000_enable_kudia_factory_for_sippar.php');
        $migration->up();
        $migration->up();

        $this->assertTrue($company->fresh()->hasFeature('kudia_factory'));
        $this->assertFalse($other->fresh()->hasFeature('kudia_factory'));
        $this->assertSame($companyCount, Company::count());
        $this->assertSame($customerCount, Customer::count());
        $this->assertSame($userCount, User::count());
        $this->assertSame(1, CompanyFeature::where('company_id', $company->id)->where('feature_key', 'kudia_factory')->count());
        $this->actingAs($user->fresh())->get('/dashboard')->assertOk();
        $this->get('/kudia')->assertOk()->assertSee('مصنع كوديا');
        $this->get('/kudia/customers')->assertOk()->assertSee($customer->name);
        $this->post('/kudia/invoices', $this->payload($customer))->assertRedirect();
        $invoice = ExternalInvoice::sole();
        $this->assertSame($company->id, $invoice->company_id);
        $this->assertSame($customer->id, $invoice->customer_id);
        $this->get('/customers/'.$customer->id)->assertNotFound();
        $this->get('/kudia/customers/'.$customer->id)->assertOk()->assertViewHas('balance', 2160000.0);
    }

    public function test_company_setup_prefills_kudia_and_creates_an_independent_manager_account(): void
    {
        $owner = User::factory()->create(['role' => 'super_admin', 'company_id' => null]);
        $this->actingAs($owner)->get('/admin/companies/create?template=kudia')
            ->assertOk()->assertSee('value="KUDIA"', false)->assertSee('value="مصنع كوديا"', false);
        $this->post('/admin/companies', [
            'name' => 'مصنع كوديا', 'code' => 'KUDIA', 'manager_name' => 'Kudia Manager',
            'manager_email' => 'kudia-manager@example.test', 'manager_password' => 'test-password-only',
            'subscription_days' => 365, 'max_users' => 5, 'status' => 'active',
        ])->assertRedirect('/admin/companies');
        $company = Company::where('code', 'KUDIA')->sole();
        $this->assertTrue($company->hasFeature('kudia_factory'));
        $manager = $company->users()->sole();
        $this->assertSame('admin', $manager->role);
        $this->actingAs($manager)->get('/dashboard')->assertRedirect('/kudia');
        $this->get('/kudia')->assertOk();
        $company->features()->create(['feature_key' => 'kudia_factory', 'enabled' => false]);
        $this->actingAs($manager->fresh())->get('/kudia')->assertForbidden();
    }

    public function test_kudia_dashboard_and_customer_pages_are_available_only_for_enabled_companies(): void
    {
        [$company, $user, $customer] = $this->records();
        $this->actingAs($user)->get('/dashboard')->assertRedirect('/kudia');
        $this->get('/kudia')->assertOk()->assertSee('مصنع كوديا');
        $this->get('/kudia/invoices/create')->assertOk()->assertSee($customer->name);
        $this->get('/kudia/customers')->assertOk()->assertSee($customer->name);
        $company->update(['code' => 'SIPPAR']);
        $this->actingAs($user->fresh())->get('/kudia')->assertForbidden();
        CompanyFeature::create(['company_id' => $company->id, 'feature_key' => 'kudia_factory', 'enabled' => true]);
        $this->actingAs($user->fresh())->get('/kudia')->assertOk();
    }

    public function test_reference_invoice_totals_and_customer_ledger_have_one_authoritative_invoice(): void
    {
        [$company, $user, $customer] = $this->records();
        $this->actingAs($user)->post('/kudia/invoices', $this->payload($customer))
            ->assertRedirect();
        $invoice = ExternalInvoice::sole();
        $this->assertSame('kudia', $invoice->provider);
        $this->assertSame('2160000.00', $invoice->amount);
        $this->assertSame('2160000.00', $invoice->subtotal);
        $this->assertSame('KUD-2026-000001', $invoice->invoice_no);
        $this->assertCount(1, $invoice->payload['items']);
        $this->assertSame(2160000.0, app(CustomerBalanceService::class)->calculate($customer)['currentBalance']);
        Receipt::create(['company_id' => $company->id, 'customer_id' => $customer->id,
            'receipt_no' => 'KUDIA-R', 'receipt_date' => '2026-10-25', 'amount' => 160000]);
        $this->get('/kudia/customers/'.$customer->id)->assertOk()->assertViewHas('balance', 2000000.0);
        $this->assertDatabaseCount('external_invoices', 1);
    }

    public function test_accountant_and_data_entry_can_use_kudia_customer_statements_without_crossing_general_role_routes(): void
    {
        [, $user, $customer] = $this->records();
        foreach (['accountant', 'data_entry'] as $role) {
            $user->update(['role' => $role]);
            $this->actingAs($user->fresh())->get('/kudia')->assertOk()->assertSee('فواتير وزبائن كوديا', false);
            $this->get('/kudia/customers/'.$customer->id)->assertOk()->assertSee('/kudia/customers/'.$customer->id.'/print', false);
            $this->get('/kudia/customers/'.$customer->id.'/print')->assertOk();
        }
    }

    public function test_invoice_update_cancellation_and_reassignment_recalculate_customer_balances(): void
    {
        [$company, $user, $customer] = $this->records();
        $this->actingAs($user)->post('/kudia/invoices', $this->payload($customer))->assertRedirect();
        $invoice = ExternalInvoice::sole();
        $number = $invoice->invoice_no;
        $destination = Customer::create(['company_id' => $company->id, 'account_group' => Customer::GROUP_KUDIA, 'name' => 'Another Kudia client']);
        $data = $this->payload($destination);
        $data['items'][0]['quantity'] = 2;
        $data['items'][0]['unit_price'] = '10.05';
        $data['tax_rate'] = 5;
        $this->put('/kudia/invoices/'.$invoice->id, $data)->assertRedirect();
        $this->assertSame($number, $invoice->fresh()->invoice_no);
        $this->assertSame('21.11', $invoice->fresh()->amount);
        $this->assertSame(0.0, app(CustomerBalanceService::class)->calculate($customer)['currentBalance']);
        $this->assertSame(21.11, app(CustomerBalanceService::class)->calculate($destination)['currentBalance']);
        $this->delete('/kudia/invoices/'.$invoice->id, ['reason' => 'Wrong invoice'])->assertRedirect();
        $this->assertSame(0.0, app(CustomerBalanceService::class)->calculate($destination)['currentBalance']);
        $this->assertDatabaseCount('external_invoices', 1);
        $this->assertSame('Wrong invoice', $invoice->fresh()->payload['cancellation']['reason']);
        $this->put('/kudia/invoices/'.$invoice->id, $data)->assertStatus(422);
        $this->get('/kudia/invoices/'.$invoice->id.'/edit')->assertStatus(422);
    }

    public function test_company_isolation_and_foreign_customer_rejection(): void
    {
        [, $user, $customer] = $this->records();
        $this->actingAs($user)->post('/kudia/invoices', $this->payload($customer))->assertRedirect();
        $invoice = ExternalInvoice::sole();
        [, $otherUser, $otherCustomer] = $this->records('OTHER');
        $this->actingAs($otherUser)->post('/kudia/invoices', $this->payload($customer))->assertSessionHasErrors('customer_id');
        $this->get('/kudia/invoices/'.$invoice->id)->assertNotFound();
        $this->get('/kudia/invoices/'.$invoice->id.'/pdf')->assertNotFound();
        $this->put('/kudia/invoices/'.$invoice->id, $this->payload($otherCustomer))->assertNotFound();
        $this->delete('/kudia/invoices/'.$invoice->id, ['reason' => 'Invalid'])->assertNotFound();
        $this->get('/kudia/customers/'.$customer->id)->assertForbidden();
        $this->assertSame('active', $invoice->fresh()->status);
        $this->assertDatabaseCount('external_invoices', 1);
    }

    public function test_validation_and_server_calculation_ignore_supplied_totals_and_currency(): void
    {
        [, $user, $customer] = $this->records();
        $this->actingAs($user)->post('/kudia/invoices', $this->payload($customer, ['items' => []]))->assertSessionHasErrors('items');
        $invalid = $this->payload($customer);
        $invalid['items'][0]['quantity'] = -1;
        $this->post('/kudia/invoices', $invalid)->assertSessionHasErrors('items.0.quantity');
        $this->assertDatabaseCount('external_invoices', 0);
        $this->post('/kudia/invoices', $this->payload($customer, ['amount' => 1, 'subtotal' => 1, 'currency' => 'USD']))->assertRedirect();
        $invoice = ExternalInvoice::sole();
        $this->assertSame('IQD', $invoice->currency);
        $this->assertSame('2160000.00', $invoice->amount);
    }

    public function test_invoice_snapshot_and_unique_numbering_survive_customer_edits(): void
    {
        [, $user, $customer] = $this->records();
        $this->actingAs($user)->post('/kudia/invoices', $this->payload($customer))->assertRedirect();
        $this->post('/kudia/invoices', $this->payload($customer))->assertRedirect();
        $this->assertSame(['KUD-2026-000001', 'KUD-2026-000002'], ExternalInvoice::orderBy('id')->pluck('invoice_no')->all());
        $customer->update(['name' => 'Renamed later']);
        $invoice = ExternalInvoice::first();
        $this->get('/kudia/invoices/'.$invoice->id.'/print')->assertOk()->assertSee('Quick Pack . co')->assertDontSee('Renamed later');
        $this->get('/kudia/invoices/'.$invoice->id)->assertOk()->assertSee('تنزيل PDF');
        $this->get('/kudia/invoices/'.$invoice->id.'/edit')->assertOk()->assertSee('White Raise Paper');
    }

    public function test_pdf_uses_kudia_artwork_and_short_invoice_fits_one_page(): void
    {
        [, $user, $customer] = $this->records();
        $this->actingAs($user)->post('/kudia/invoices', $this->payload($customer))->assertRedirect();
        $invoice = ExternalInvoice::sole();
        $html = $this->get('/kudia/invoices/'.$invoice->id.'/print')->assertOk()
            ->assertSee('KUDIA')->assertSee('CLIENT INFO:')->assertSee('ITEM DESCRIPTION')
            ->assertSee('2,160,000')->assertDontSee('AL WALEED ERP');
        $pdf = $this->get('/kudia/invoices/'.$invoice->id.'/pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        preg_match_all('/\/Type\s*\/Page\b/', $pdf->getContent(), $pages);
        $this->assertCount(1, $pages[0]);
    }

    public function test_long_invoice_pdf_keeps_all_line_items_and_paginates(): void
    {
        [, $user, $customer] = $this->records();
        $data = $this->payload($customer);
        $data['items'] = array_fill(0, 35, $data['items'][0]);
        $this->actingAs($user)->post('/kudia/invoices', $data)->assertRedirect();
        $invoice = ExternalInvoice::sole();
        $this->assertCount(35, $invoice->payload['items']);
        $pdf = $this->get('/kudia/invoices/'.$invoice->id.'/pdf')->assertOk();
        preg_match_all('/\/Type\s*\/Page\b/', $pdf->getContent(), $pages);
        $this->assertGreaterThan(1, count($pages[0]));
    }

    private function records(string $suffix = ''): array
    {
        $company = Company::create(['name' => 'مصنع كوديا', 'code' => $suffix ?: 'KUDIA', 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addYear()]);
        if ($suffix) {
            CompanyFeature::create(['company_id' => $company->id, 'feature_key' => 'kudia_factory', 'enabled' => true]);
        }
        Setting::create(['company_id' => $company->id, 'company_name' => 'مصنع كوديا', 'currency' => 'IQD']);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'account_group' => Customer::GROUP_KUDIA, 'name' => 'Quick Pack . co', 'address' => 'Iraq - Erbil']);

        return [$company, $user, $customer];
    }

    private function payload(Customer $customer, array $overrides = []): array
    {
        return array_replace([
            'customer_id' => $customer->id, 'invoice_date' => '2026-10-24', 'tax_rate' => 0,
            'items' => [['description' => 'White Raise Paper', 'details' => '25 × 35', 'unit_price' => 15000, 'quantity' => 144, 'unit' => 'cartons']],
            'payment_info' => '', 'notes' => '',
        ], $overrides);
    }
}
