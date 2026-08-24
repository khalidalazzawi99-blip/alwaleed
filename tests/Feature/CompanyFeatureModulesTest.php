<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\Cashbox;
use App\Models\FeatureModuleRecord;
use App\Models\Receipt;
use App\Models\User;
use App\Models\VoucherAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyFeatureModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_module_is_blocked_and_enabled_module_is_available(): void
    {
        [$company, $user] = $this->companyUser();
        $this->actingAs($user)->get('/modules/inventory')->assertForbidden();

        CompanyFeature::create(['company_id' => $company->id, 'feature_key' => 'inventory', 'enabled' => true]);
        $this->actingAs($user)->get('/modules/inventory')->assertOk();
        $this->actingAs($user)->post('/modules/inventory', [
            'reference' => 'ITEM-1', 'name' => 'مادة اختبار', 'record_date' => now()->toDateString(),
            'amount' => 25, 'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('feature_module_records', [
            'company_id' => $company->id, 'module' => 'inventory', 'reference' => 'ITEM-1',
        ]);
    }

    public function test_company_cannot_edit_another_company_module_record(): void
    {
        [$company, $user] = $this->companyUser();
        CompanyFeature::create(['company_id' => $company->id, 'feature_key' => 'projects', 'enabled' => true]);
        [$other] = $this->companyUser('OTHER');
        $record = FeatureModuleRecord::create([
            'company_id' => $other->id, 'module' => 'projects', 'name' => 'خاص',
            'record_date' => now(), 'amount' => 1, 'status' => 'active',
        ]);

        $this->actingAs($user)->delete('/modules/projects/'.$record->id)->assertNotFound();
    }

    public function test_voucher_attachment_is_saved_for_company_voucher(): void
    {
        Storage::fake('local');
        [$company, $user] = $this->companyUser();
        CompanyFeature::create(['company_id' => $company->id, 'feature_key' => 'voucher_attachments', 'enabled' => true]);
        $receipt = Receipt::create([
            'company_id' => $company->id, 'receipt_no' => 'R-1', 'receipt_date' => now(), 'amount' => 10,
        ]);

        $this->actingAs($user)->post('/voucher-attachments', [
            'voucher_type' => 'receipt', 'voucher_id' => $receipt->id,
            'attachment' => UploadedFile::fake()->create('invoice.pdf', 20, 'application/pdf'),
        ])->assertRedirect();

        $this->assertDatabaseHas('voucher_attachments', ['company_id' => $company->id, 'voucher_id' => $receipt->id]);
    }

    public function test_company_cannot_download_another_company_voucher_attachment(): void
    {
        Storage::fake('local');
        [$company, $user] = $this->companyUser();
        CompanyFeature::create(['company_id' => $company->id, 'feature_key' => 'voucher_attachments', 'enabled' => true]);
        [$other] = $this->companyUser('OTHER');
        $attachment = VoucherAttachment::create([
            'company_id' => $other->id,
            'voucher_type' => 'receipt',
            'voucher_id' => 1,
            'original_name' => 'private.pdf',
            'path' => 'voucher-attachments/private.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10,
        ]);

        Storage::disk('local')->put($attachment->path, 'private');

        $this->actingAs($user)
            ->get('/voucher-attachments/'.$attachment->id.'/download')
            ->assertNotFound();
    }

    public function test_multiple_cashboxes_can_be_managed_safely(): void
    {
        [$company, $user] = $this->companyUser();
        CompanyFeature::create(['company_id' => $company->id, 'feature_key' => 'multiple_cashboxes', 'enabled' => true]);
        $main = Cashbox::create(['company_id' => $company->id, 'name' => 'الرئيسي', 'balance' => 0]);

        $this->actingAs($user)->post('/cashbox', [
            'name' => 'الفرعي',
            'balance' => 0,
        ])->assertRedirect();

        $secondary = Cashbox::where('company_id', $company->id)->where('name', 'الفرعي')->firstOrFail();
        $this->actingAs($user)->delete('/cashbox/'.$secondary->id)->assertRedirect();
        $this->assertDatabaseMissing('cashboxes', ['id' => $secondary->id]);

        $this->actingAs($user)->delete('/cashbox/'.$main->id)->assertStatus(422);
        $this->assertDatabaseHas('cashboxes', ['id' => $main->id]);
    }

    private function companyUser(string $code = 'TEST'): array
    {
        $company = Company::create([
            'name' => 'شركة '.$code, 'code' => $code.uniqid(), 'status' => 'active',
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth(), 'max_users' => 10,
        ]);
        $user = User::create([
            'company_id' => $company->id, 'name' => 'مدير', 'email' => uniqid().'@example.test',
            'password' => 'password', 'role' => 'admin',
        ]);
        return [$company, $user];
    }
}
