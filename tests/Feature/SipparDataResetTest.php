<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SipparDataResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_retired_reset_migration_is_a_noop_for_sippar_data(): void
    {
        $sippar = Company::create([
            'name' => 'Sippar lights',
            'code' => 'SIPPAR',
            'status' => 'active',
            'subscription_start' => now()->subDay(),
            'subscription_end' => now()->addMonth(),
        ]);
        $customer = Customer::create([
            'company_id' => $sippar->id,
            'name' => 'Data that must remain',
        ]);
        $receipt = Receipt::create([
            'company_id' => $sippar->id,
            'customer_id' => $customer->id,
            'receipt_no' => 'SAFE-1',
            'receipt_date' => now(),
            'amount' => 500,
        ]);

        $migration = $this->migration();
        $migration->up();
        $migration->down();

        $this->assertDatabaseHas('companies', ['id' => $sippar->id, 'code' => 'SIPPAR']);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'company_id' => $sippar->id]);
        $this->assertDatabaseHas('receipts', ['id' => $receipt->id, 'company_id' => $sippar->id]);
        $this->assertFalse(Schema::hasTable('company_data_reset_backups'));
    }

    public function test_retired_reset_migration_is_a_noop_without_sippar(): void
    {
        $company = Company::create([
            'name' => 'Other Company',
            'code' => 'OTHER',
            'status' => 'active',
        ]);

        $this->migration()->up();

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'code' => 'OTHER']);
        $this->assertFalse(Schema::hasTable('company_data_reset_backups'));
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_09_09_235000_reset_sippar_business_data_once.php');
    }
}
