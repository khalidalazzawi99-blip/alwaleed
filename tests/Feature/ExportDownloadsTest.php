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

    public function test_every_pdf_and_excel_export_downloads_a_valid_file(): void
    {
        [$user, $customer, $supplier, $receipt, $payment] = $this->records();
        $this->actingAs($user);

        foreach (["/receipts/{$receipt->id}/pdf", "/payments/{$payment->id}/pdf", '/reports/pdf', "/customers/{$customer->id}/pdf", "/suppliers/{$supplier->id}/pdf"] as $route) {
            $response = $this->get($route)->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent(), $route);
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
