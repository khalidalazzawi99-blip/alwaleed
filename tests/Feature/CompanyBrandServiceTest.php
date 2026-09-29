<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Setting;
use App\Services\CompanyBrandService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompanyBrandServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_company_logo_is_embedded_for_print_and_pdf_views(): void
    {
        Storage::fake(config('filesystems.company_logo_disk'));
        $company = Company::create([
            'name' => 'Logo Test Company',
            'code' => 'LOGO'.Str::upper(Str::random(6)),
            'status' => 'active',
        ]);
        Storage::disk(config('filesystems.company_logo_disk'))->put('logos/company.png', 'test-image-contents');
        Setting::create([
            'company_id' => $company->id,
            'company_name' => $company->name,
            'company_name_ar' => 'شركة الاختبار',
            'company_name_en' => 'Test Company',
            'company_logo' => 'logos/company.png',
            'email' => 'must-not-appear@example.test',
            'currency' => 'IQD',
        ]);

        $logo = app(CompanyBrandService::class)->logoDataUri($company->id);

        $this->assertStringStartsWith('data:', $logo);
        $this->assertStringContainsString(';base64,'.base64_encode('test-image-contents'), $logo);
        $this->assertArrayNotHasKey('email', app(CompanyBrandService::class)->profile($company->id));
        $this->assertArrayNotHasKey('password', app(CompanyBrandService::class)->profile($company->id));

        $header = view('documents._header', ['companyId' => $company->id, 'documentTitle' => 'سند اختبار'])->render();
        $this->assertStringContainsString('dir="rtl">شركة الاختبار', $header);
        $this->assertStringContainsString('dir="ltr">Test Company', $header);
        $this->assertStringNotContainsString('must-not-appear@example.test', $header);
    }
}
