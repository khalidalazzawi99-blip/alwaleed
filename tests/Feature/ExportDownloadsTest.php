<?php

namespace Tests\Feature;

use App\Models\Cashbox;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExportDownloadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_statement_pdf_positions_arabic_headings_and_first_column_on_the_right_with_readable_type(): void
    {
        [$user, $customer] = $this->records();
        $frames = [];
        $this->app->resolving('dompdf', function ($renderer) use (&$frames) {
            $renderer->setCallbacks([['event' => 'end_frame', 'f' => function ($frame) use (&$frames) {
                $node = $frame->get_node();
                if (! $node instanceof \DOMElement) {
                    return;
                }
                foreach (['title-cell', 'brand-cell', 'document-title', 'hero-main', 'hero-meta', 'sequence', 'movement-notes', 'amount'] as $class) {
                    if (in_array($class, explode(' ', $node->getAttribute('class')), true)) {
                        $frames[$class][] = [
                            'x' => $frame->get_position()['x'],
                            'align' => $frame->get_style()->text_align,
                            'fontSize' => $frame->get_style()->font_size,
                        ];
                    }
                }
            }]]);
        });

        $this->actingAs($user)->get('/customers/'.$customer->id.'/pdf')
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertGreaterThan($frames['brand-cell'][0]['x'], $frames['title-cell'][0]['x']);
        $this->assertGreaterThan($frames['hero-meta'][0]['x'], $frames['hero-main'][0]['x']);
        $this->assertGreaterThan($frames['movement-notes'][0]['x'], $frames['sequence'][0]['x']);
        $this->assertSame('right', $frames['document-title'][0]['align']);
        $this->assertSame('right', $frames['hero-main'][0]['align']);
        foreach ($frames['amount'] as $amount) {
            $this->assertGreaterThanOrEqual(9, $amount['fontSize']);
        }
    }

    public function test_every_pdf_and_excel_export_downloads_a_valid_file(): void
    {
        [$user, $customer, $supplier, $receipt, $payment] = $this->records();
        $this->actingAs($user);

        foreach (["/receipts/{$receipt->id}/pdf", "/payments/{$payment->id}/pdf", '/reports/pdf', "/customers/{$customer->id}/pdf", "/suppliers/{$supplier->id}/pdf"] as $route) {
            $response = $this->get($route)->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent(), $route);
            if ($route !== '/reports/pdf') {
                preg_match_all('/\/Type\s*\/Page\b/', $response->getContent(), $pages);
                $this->assertCount(1, $pages[0], 'A short document must fit on one page: '.$route);
            }
        }

        foreach (["/receipts/{$receipt->id}/excel", "/payments/{$payment->id}/excel", '/reports/excel', "/customers/{$customer->id}/excel", "/suppliers/{$supplier->id}/excel"] as $index => $route) {
            $response = $this->get($route)->assertOk();
            $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string) $response->headers->get('content-type'), $route);
            $bytes = $response->streamedContent();
            $this->assertStringStartsWith('PK', $bytes, $route);

            if ($index === 0) {
                $path = tempnam(sys_get_temp_dir(), 'alwaleed-export-');
                file_put_contents($path, $bytes);
                $sheet = IOFactory::load($path)->getActiveSheet();
                $this->assertSame('سند قبض', $sheet->getCell('B1')->getValue());
                $this->assertSame('R-100', $sheet->getCell('A6')->getValue());
                $this->assertSame('زبون عربي', $sheet->getCell('C6')->getValue());
                $this->assertTrue($sheet->getRightToLeft());
                unlink($path);
            }
        }
    }

    public function test_long_statements_paginate_without_clipping_the_table(): void
    {
        [$user, $customer] = $this->records();
        for ($i = 1; $i <= 60; $i++) {
            Receipt::create([
                'company_id' => $customer->company_id,
                'customer_id' => $customer->id,
                'receipt_no' => 'R-LONG-'.$i,
                'receipt_date' => now(),
                'amount' => 100,
                'notes' => 'Statement movement '.$i,
            ]);
        }

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/pdf")->assertOk();
        preg_match_all('/\/Type\s*\/Page\b/', $response->getContent(), $pages);
        $this->assertGreaterThan(1, count($pages[0]));
    }

    private function records(): array
    {
        $company = Company::create(['name' => 'شركة الاختبار', 'code' => 'EXPORT', 'status' => 'active', 'max_users' => 5, 'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth()]);
        $user = User::create(['company_id' => $company->id, 'name' => 'مدير', 'email' => 'export@example.test', 'password' => 'password', 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'زبون عربي', 'phone' => '123']);
        $supplier = Supplier::create(['company_id' => $company->id, 'name' => 'مورد عربي', 'phone' => '456']);
        $receipt = Receipt::create(['company_id' => $company->id, 'customer_id' => $customer->id, 'receipt_no' => 'R-100', 'receipt_date' => now(), 'amount' => 1500, 'notes' => 'ملاحظة قبض']);
        $payment = Payment::create(['company_id' => $company->id, 'supplier_id' => $supplier->id, 'payment_no' => 'P-100', 'payment_date' => now(), 'amount' => 500, 'notes' => 'ملاحظة صرف']);
        Cashbox::create(['company_id' => $company->id, 'name' => 'الرئيسي', 'balance' => 1000]);
        return [$user, $customer, $supplier, $receipt, $payment];
    }
}
