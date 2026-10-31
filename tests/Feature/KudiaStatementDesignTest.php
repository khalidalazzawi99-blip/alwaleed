<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\Customer;
use App\Models\ExternalInvoice;
use App\Models\PartyDebtTransaction;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KudiaStatementDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_statement_shows_invoice_descriptions_payments_and_the_correct_balance(): void
    {
        [$user, $customer] = $this->records();
        $this->referenceMovements($customer);
        $this->actingAs($user)->get('/kudia/customers/'.$customer->id.'/print')->assertOk()
            ->assertViewIs('kudia.statement')->assertSee('STATEMENT OF')->assertSee('ACCOUNT')
            ->assertSee('CLIENT INFO:')->assertSee('REF NO.')->assertSee('DESCRIPTION')->assertSee('DEBIT')
            ->assertSee('PAYMENT RECEIVED')->assertSee('BALANCE DUE')->assertSee('Brown Raise Paper 25 × 35')
            ->assertSee('14,173,500')->assertSee('10,000,000')->assertSee('4,173,500')
            ->assertSee('Customer Payment')->assertSee('Partial payment received from customer.')
            ->assertViewHas('totalInvoiceAmount', 14173500.0)->assertViewHas('balance', 4173500.0)
            ->assertDontSee('AL WALEED ERP');
        $general = Customer::create(['company_id' => $customer->company_id, 'name' => 'Sippar customer']);
        $this->get('/customers/'.$general->id.'/print')->assertOk()->assertViewIs('customers.print')->assertDontSee('STATEMENT OF');
    }

    public function test_filtered_statement_keeps_opening_balance_and_counts_only_period_transactions(): void
    {
        [$user, $customer] = $this->records();
        $this->referenceMovements($customer);
        $this->actingAs($user)->get('/kudia/customers/'.$customer->id.'/print?from=2026-10-24&to=2026-10-28')
            ->assertOk()->assertViewHas('openingBalance', 12013500.0)
            ->assertViewHas('totalInvoiceAmount', 2160000.0)->assertViewHas('balance', 4173500.0)
            ->assertSee('Opening Balance')->assertSee('12,013,500')->assertSee('4,173,500');
    }

    public function test_other_debits_and_debt_payments_are_shown_without_mislabeling_them_as_invoices(): void
    {
        [$user, $customer] = $this->records();
        $this->referenceMovements($customer);
        foreach (['borrowing' => 100, 'debt_payment' => 25] as $type => $amount) {
            PartyDebtTransaction::create(['company_id' => $customer->company_id, 'customer_id' => $customer->id,
                'type' => $type, 'amount' => $amount, 'transaction_date' => '2026-10-29']);
        }
        Payment::create(['company_id' => $customer->company_id, 'customer_id' => $customer->id,
            'payment_no' => 'PAY-REFUND', 'payment_date' => '2026-10-29', 'amount' => 10]);
        $this->actingAs($user)->get('/kudia/customers/'.$customer->id.'/print')->assertOk()
            ->assertViewHas('totalInvoiceAmount', 14173500.0)->assertViewHas('balance', 4173585.0)
            ->assertSee('OTHER DEBITS')->assertSee('Customer Refund')->assertSee('Debt Payment');
    }

    public function test_reference_pdfs_fit_one_page_with_real_inset_margins_and_table_columns_inside_the_page(): void
    {
        [$user, $customer] = $this->records();
        $this->referenceMovements($customer);
        $boxes = [];
        $amountBoxes = [];
        $this->app->resolving('dompdf', function ($renderer) use (&$boxes, &$amountBoxes) {
            $renderer->setCallbacks([['event' => 'end_frame', 'f' => function ($frame) use (&$boxes, &$amountBoxes) {
                $node = $frame->get_node();
                if ($node instanceof \DOMElement && str_contains($node->getAttribute('class'), 'kudia-grid ')) {
                    $boxes[] = $frame->get_border_box();
                }
                if ($node instanceof \DOMText && trim($node->nodeValue) !== ''
                    && $node->parentNode instanceof \DOMElement && str_contains($node->parentNode->getAttribute('class'), 'cell money')) {
                    $cell = $frame->get_parent();
                    while ($cell && $cell->get_node()->nodeName !== 'td') {
                        $cell = $cell->get_parent();
                    }
                    if ($cell) {
                        $amountBoxes[] = ['text' => $frame->get_border_box(), 'cell' => $cell->get_border_box()];
                    }
                }
            }]]);
        });
        $this->actingAs($user);
        foreach (['/kudia/customers/'.$customer->id.'/pdf', '/kudia/invoices/'.ExternalInvoice::latest('id')->first()->id.'/pdf'] as $path) {
            $boxes = [];
            $amountBoxes = [];
            $pdf = $this->get($path)->assertOk()->assertHeader('content-type', 'application/pdf');
            preg_match_all('/\/Type\s*\/Page\b/', $pdf->getContent(), $pages);
            $this->assertCount(1, $pages[0], $path);
            $this->assertNotEmpty($boxes);
            $minimum = str_contains($path, '/invoices/') ? 70 : 22;
            foreach ($boxes as $box) {
                $this->assertGreaterThanOrEqual($minimum, $box['x']);
                $this->assertLessThanOrEqual(595.28 - $minimum + 1, $box['x'] + $box['w']);
            }
            $this->assertNotEmpty($amountBoxes);
            foreach ($amountBoxes as $amountBox) {
                $this->assertGreaterThanOrEqual($amountBox['cell']['x'] - 1, $amountBox['text']['x']);
                $this->assertLessThanOrEqual($amountBox['cell']['x'] + $amountBox['cell']['w'] + 1, $amountBox['text']['x'] + $amountBox['text']['w']);
            }
        }
        $this->assertDatabaseCount('external_invoices', 3);
        $this->assertDatabaseCount('receipts', 1);
    }

    public function test_long_statement_paginates_and_keeps_all_movements(): void
    {
        [$user, $customer] = $this->records();
        for ($i = 1; $i <= 35; $i++) {
            $this->invoice($customer, $i, '2026-10-20', 10);
        }
        $this->actingAs($user)->get('/kudia/customers/'.$customer->id.'/print')->assertOk()
            ->assertViewHas('movementsCount', 35)->assertViewHas('balance', 350.0);
        $pdf = $this->get('/kudia/customers/'.$customer->id.'/pdf')->assertOk();
        preg_match_all('/\/Type\s*\/Page\b/', $pdf->getContent(), $pages);
        $this->assertGreaterThan(1, count($pages[0]));
    }

    private function records(): array
    {
        $company = Company::create(['name' => 'Sippar', 'code' => 'SIPPAR', 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addYear()]);
        CompanyFeature::create(['company_id' => $company->id, 'feature_key' => 'kudia_factory', 'enabled' => true]);
        Setting::create(['company_id' => $company->id, 'company_name' => 'Sippar', 'currency' => 'IQD']);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'account_group' => 'kudia', 'name' => 'Quick Pack . co', 'address' => 'Iraq - Erbil']);

        return [$user, $customer];
    }

    private function referenceMovements(Customer $customer): void
    {
        $this->invoice($customer, 3, '2026-09-28', 10933500, true);
        $this->invoice($customer, 4, '2026-10-12', 1080000);
        $this->invoice($customer, 5, '2026-10-24', 2160000);
        Receipt::create(['company_id' => $customer->company_id, 'customer_id' => $customer->id,
            'receipt_no' => 'RCP-2026-000001', 'receipt_date' => '2026-10-28', 'amount' => 10000000,
            'notes' => 'Partial payment received from customer.']);
    }

    private function invoice(Customer $customer, int $number, string $date, float $amount, bool $twoItems = false): void
    {
        $items = [['description' => 'White Raise Paper', 'details' => '25 × 35', 'unit_price' => $amount, 'quantity' => 1, 'total' => $amount, 'unit' => 'cartons']];
        if ($twoItems) {
            $items[] = ['description' => 'Brown Raise Paper', 'details' => '25 × 35', 'unit_price' => 0, 'quantity' => 1, 'total' => 0, 'unit' => 'cartons'];
        }
        ExternalInvoice::create(['company_id' => $customer->company_id, 'customer_id' => $customer->id,
            'external_customer_id' => $customer->integration_id, 'external_invoice_id' => 'kudia-'.$number,
            'invoice_no' => sprintf('KUD-2026-%06d', $number), 'invoice_date' => $date, 'currency' => 'IQD',
            'provider' => 'kudia', 'status' => 'active', 'amount' => $amount, 'subtotal' => $amount,
            'payload' => ['items' => $items, 'tax_amount' => 0, 'client_name' => $customer->name, 'client_address' => $customer->address]]);
    }
}
