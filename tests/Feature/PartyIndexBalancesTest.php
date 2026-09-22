<?php

namespace Tests\Feature;

use App\Models\{Company, Customer, Supplier, User, Receipt, Payment, ExternalInvoice};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartyIndexBalancesTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_can_be_filtered_to_outstanding_balances_and_exported_to_pdf(): void
    {
        $company = Company::create(['name' => 'Debtors', 'code' => 'DEBTORS', 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth()]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $debtor = Customer::create(['company_id' => $company->id, 'name' => 'Outstanding Customer']);
        $settled = Customer::create(['company_id' => $company->id, 'name' => 'Settled Customer']);
        $otherCompany = Company::create(['name' => 'Other debtors', 'code' => 'OTHER-DEBT', 'status' => 'active']);
        $hidden = Customer::create(['company_id' => $otherCompany->id, 'name' => 'Hidden Debtor']);

        foreach ([[$debtor, $company, 750], [$hidden, $otherCompany, 999]] as [$customer, $owner, $amount]) {
            ExternalInvoice::create([
                'company_id' => $owner->id,
                'customer_id' => $customer->id,
                'external_invoice_id' => 'debt-'.$customer->id,
                'external_customer_id' => $customer->integration_id,
                'invoice_no' => 'INV-'.$customer->id,
                'invoice_date' => now(),
                'amount' => $amount,
                'status' => 'active',
            ]);
        }

        $response = $this->actingAs($user)->get('/customers?debts_only=1')->assertOk();
        $customers = $response->viewData('customers');
        $this->assertCount(1, $customers);
        $this->assertTrue($customers->first()->is($debtor));
        $response->assertSee('Outstanding Customer')->assertDontSee('Settled Customer')->assertDontSee('Hidden Debtor');

        $pdf = $this->get('/customers/debtors/pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
    }

    public function test_lists_show_paid_and_remaining_amounts_matching_statements(): void
    {
        $company = Company::create(['name' => 'Balances', 'code' => 'BAL', 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth()]);
        $other = Company::create(['name' => 'Other', 'code' => 'OTHER', 'status' => 'active']);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

        foreach ([Customer::class, Supplier::class] as $model) {
            $isCustomer = $model === Customer::class;
            $route = $isCustomer ? 'customers' : 'suppliers';
            $foreignKey = $isCustomer ? 'customer_id' : 'supplier_id';
            $party = $model::create(['company_id' => $company->id, 'name' => 'With movements']);
            $empty = $model::create(['company_id' => $company->id, 'name' => 'No movements']);
            $hidden = $model::create(['company_id' => $other->id, 'name' => 'Other company party']);
            Receipt::create(['company_id' => $company->id, $foreignKey => $party->id,
                'receipt_no' => 'R-'.$route, 'receipt_date' => now(), 'amount' => 250.25]);
            Payment::create(['company_id' => $company->id, $foreignKey => $party->id,
                'payment_no' => 'P-'.$route, 'payment_date' => now(), 'amount' => 100.50]);
            // Even incorrectly linked vouchers must not leak another company's totals.
            Receipt::create(['company_id' => $other->id, $foreignKey => $party->id,
                'receipt_no' => 'OTHER-'.$route, 'receipt_date' => now(), 'amount' => 9999]);
            Payment::create(['company_id' => $other->id, $foreignKey => $party->id,
                'payment_no' => 'OTHER-'.$route, 'payment_date' => now(), 'amount' => 8888]);

            $check = function (float $remaining) use ($user, $route, $party, $empty, $hidden, $isCustomer) {
                $response = $this->actingAs($user)->get('/'.$route)->assertOk()
                    ->assertSee('المبلغ الباقي')->assertSee('المبلغ المدفوع')
                    ->assertSee(number_format($remaining, 2));
                $rows = $response->viewData($route)->keyBy('id');
                $this->assertCount(2, $rows);
                $this->assertFalse($rows->has($hidden->id));
                $this->assertEquals($remaining, $rows[$party->id]->remaining_amount);
                $this->assertEquals($isCustomer ? 250.25 : 100.50, $rows[$party->id]->paid_amount);
                $this->assertEquals(0, $rows[$empty->id]->remaining_amount);
                $this->assertEquals(0, $rows[$empty->id]->paid_amount);
                $this->actingAs($user)->get('/'.$route.'/'.$party->id)->assertOk()
                    ->assertViewHas('balance', $remaining);
            };

            $check($isCustomer ? -149.75 : 149.75);
            if ($isCustomer) {
                foreach ([['active', 1000], ['cancelled', 5000]] as [$status, $amount]) {
                    ExternalInvoice::create(['company_id' => $company->id, 'customer_id' => $party->id,
                        'external_invoice_id' => $status, 'external_customer_id' => $party->integration_id,
                        'invoice_no' => 'INV-'.$status, 'invoice_date' => now(), 'amount' => $amount, 'status' => $status]);
                }
                $check(850.25);
                $party->externalInvoices()->where('status', 'active')->update(['amount' => 100]);
                $check(-49.75);
            }
        }
    }
}
