<?php

namespace Tests\Feature;

use App\Models\{Account, Cashbox, CashboxLog, Company, CompanyFeature, Customer, ExternalInvoice, Payment, Receipt, Setting, Supplier, User, VoucherAttachment};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SipparDataResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_backs_up_and_removes_only_sippar_operational_data_once(): void
    {
        Storage::fake('local');
        [$sippar, $user, $customer, $box, $account] = $this->companyData('SIPPAR', 'Sippar lights');
        [$other, $otherUser, $otherCustomer, $otherBox, $otherAccount] = $this->companyData('OTHER', 'Other Company');
        $preserved = [
            'company' => DB::table('companies')->where('id', $sippar->id)->first(),
            'user' => DB::table('users')->where('id', $user->id)->first(),
            'settings' => DB::table('settings')->where('company_id', $sippar->id)->get()->all(),
            'features' => DB::table('company_features')->where('company_id', $sippar->id)->get()->all(),
        ];
        $otherBefore = $this->tenantSnapshot($other->id, $otherAccount->id);
        $this->migration()->up();
        foreach (['customers', 'suppliers', 'receipts', 'payments', 'cashbox_logs', 'external_invoices',
            'accounts', 'voucher_attachments', 'feature_module_records', 'activity_logs'] as $table) {
            $this->assertSame(0, DB::table($table)->where('company_id', $sippar->id)->count(), $table);
        }
        $this->assertSame(0, DB::table('transactions')->where('account_id', $account->id)->count());
        $this->assertSame(0, DB::table('cashbox_customer')->where('cashbox_id', $box->id)->count());
        $freshCashbox = Cashbox::where('company_id', $sippar->id)->sole();
        $this->assertSame('cash', $freshCashbox->account_type);
        $this->assertEquals(0, $freshCashbox->balance);
        $this->assertNull($freshCashbox->account_number);
        $this->assertNull($freshCashbox->bank_name);
        $this->assertEquals($preserved['company'], DB::table('companies')->where('id', $sippar->id)->first());
        $this->assertEquals($preserved['user'], DB::table('users')->where('id', $user->id)->first());
        $this->assertEquals($preserved['settings'], DB::table('settings')->where('company_id', $sippar->id)->get()->all());
        $this->assertEquals($preserved['features'], DB::table('company_features')->where('company_id', $sippar->id)->get()->all());
        $this->assertSame($otherBefore, $this->tenantSnapshot($other->id, $otherAccount->id));

        $backup = DB::table('company_data_reset_backups')->sole();
        $this->assertNotNull($backup->completed_at);
        $this->assertStringNotContainsString('Sippar lights', $backup->encrypted_payload);
        $snapshot = json_decode(Crypt::decryptString($backup->encrypted_payload), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($customer->id, $snapshot['tables']['customers'][0]['id']);
        $this->assertEquals(1234.50, $snapshot['tables']['cashboxes'][0]['balance']);
        $path = 'voucher-attachments/'.$sippar->id.'/test.pdf';
        $this->assertSame('test attachment', base64_decode($snapshot['files'][$path]));
        Storage::disk('local')->assertMissing($path);
        Storage::disk('local')->assertExists('voucher-attachments/'.$other->id.'/test.pdf');

        $newCustomer = Customer::create(['company_id' => $sippar->id, 'name' => 'New data after reset']);
        $this->migration()->up();
        $this->assertDatabaseHas('customers', ['id' => $newCustomer->id]);
        $this->assertSame($freshCashbox->id, Cashbox::where('company_id', $sippar->id)->sole()->id);
        $this->assertDatabaseCount('company_data_reset_backups', 1);
    }

    public function test_foreign_links_stop_reset_without_deleting_any_data(): void
    {
        Storage::fake('local');
        [$sippar, , $customer, , $account] = $this->companyData('SIPPAR', 'Sippar lights');
        [$other] = $this->companyData('OTHER', 'Other Company');
        Receipt::create(['company_id' => $other->id, 'customer_id' => $customer->id, 'amount' => 5,
            'receipt_no' => 'INVALID', 'receipt_date' => now()]);
        $before = $this->tenantSnapshot($sippar->id, $account->id);
        try {
            $this->migration()->up();
            $this->fail('Expected reset to refuse cross-company links.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('cross-company', $exception->getMessage());
        }
        $this->assertSame($before, $this->tenantSnapshot($sippar->id, $account->id));
        $this->assertDatabaseCount('company_data_reset_backups', 0);
        Storage::disk('local')->assertExists('voucher-attachments/'.$sippar->id.'/test.pdf');
    }

    public function test_no_matching_code_is_a_noop_and_wrong_identity_is_rejected(): void
    {
        Storage::fake('local');
        [$company, , , , $account] = $this->companyData('DIFFERENT', 'Sippar lights');
        $before = $this->tenantSnapshot($company->id, $account->id);
        $this->migration()->up();
        $this->assertSame($before, $this->tenantSnapshot($company->id, $account->id));
        $this->assertDatabaseCount('company_data_reset_backups', 0);
        $company->update(['code' => 'SIPPAR', 'name' => 'Different Tenant']);
        try {
            $this->migration()->up();
            $this->fail('Expected identity mismatch to stop the reset.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('identity', $exception->getMessage());
        }
        $this->assertSame(1, Customer::where('company_id', $company->id)->count());
        $this->assertDatabaseCount('company_data_reset_backups', 0);
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_09_09_235000_reset_sippar_business_data_once.php');
    }

    private function tenantSnapshot(int $companyId, int $accountId): string
    {
        $snapshot = [];
        foreach (['companies', 'users', 'settings', 'company_features', 'customers', 'suppliers', 'cashboxes',
            'accounts', 'receipts', 'payments', 'cashbox_logs', 'external_invoices', 'voucher_attachments',
            'feature_module_records', 'activity_logs'] as $table) {
            $snapshot[$table] = DB::table($table)->where($table === 'companies' ? 'id' : 'company_id', $companyId)->orderBy('id')->get();
        }
        $snapshot['transactions'] = DB::table('transactions')->where('account_id', $accountId)->get();
        $snapshot['links'] = DB::table('cashbox_customer')->whereIn('cashbox_id', DB::table('cashboxes')->where('company_id', $companyId)->select('id'))->get();
        return json_encode($snapshot, JSON_THROW_ON_ERROR);
    }

    private function companyData(string $code, string $name): array
    {
        $company = Company::create(['name' => $name, 'code' => $code, 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth()]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        Setting::create(['company_id' => $company->id, 'company_name' => $name, 'currency' => 'USD']);
        CompanyFeature::create(['company_id' => $company->id, 'feature_key' => 'multiple_cashboxes', 'enabled' => true]);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'Customer '.$code]);
        $supplier = Supplier::create(['company_id' => $company->id, 'name' => 'Supplier '.$code]);
        $box = Cashbox::create(['company_id' => $company->id, 'name' => 'Bank '.$code, 'account_type' => 'bank',
            'bank_name' => 'Bank', 'account_number' => '123456', 'balance' => 1234.50, 'is_active' => true]);
        $box->customers()->attach($customer);
        $account = Account::forceCreate(['company_id' => $company->id, 'name' => 'Account', 'type' => 'asset', 'balance' => 20]);
        DB::table('transactions')->insert(['account_id' => $account->id, 'type' => 'income', 'amount' => 20,
            'category' => 'test', 'transaction_date' => now()->toDateString()]);
        $receipt = Receipt::create(['company_id' => $company->id, 'cashbox_id' => $box->id,
            'customer_id' => $customer->id, 'receipt_no' => 'R-'.$code, 'receipt_date' => now(), 'amount' => 50]);
        Payment::create(['company_id' => $company->id, 'cashbox_id' => $box->id,
            'supplier_id' => $supplier->id, 'payment_no' => 'P-'.$code, 'payment_date' => now(), 'amount' => 10]);
        CashboxLog::create(['company_id' => $company->id, 'cashbox_id' => $box->id, 'type' => 'إيداع مباشر',
            'reference_no' => 'D-'.$code, 'person_name' => 'test', 'amount' => 25, 'balance_after' => 1234.50]);
        ExternalInvoice::create(['company_id' => $company->id, 'customer_id' => $customer->id,
            'external_invoice_id' => 'EXT-'.$code, 'external_customer_id' => $customer->integration_id,
            'invoice_no' => 'INV-'.$code, 'invoice_date' => now(), 'amount' => 75]);
        DB::table('feature_module_records')->insert(['company_id' => $company->id, 'module' => 'inventory',
            'name' => 'Stock', 'record_date' => now()->toDateString(), 'amount' => 50]);
        $path = 'voucher-attachments/'.$company->id.'/test.pdf';
        Storage::disk('local')->put($path, 'test attachment');
        VoucherAttachment::create(['company_id' => $company->id, 'voucher_type' => 'receipt', 'voucher_id' => $receipt->id,
            'original_name' => 'test.pdf', 'path' => $path, 'size' => 15]);
        return [$company, $user, $customer, $box, $account];
    }
}
