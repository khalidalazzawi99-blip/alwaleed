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
        Storage::fake('public');
        $company = Company::create([
            'name' => 'Logo Test Company',
            'code' => 'LOGO'.Str::upper(Str::random(6)),
            'status' => 'active',
        ]);
        Storage::disk('public')->put('logos/company.png', 'test-image-contents');
        Setting::create([
            'company_id' => $company->id,
            'company_name' => $company->name,
            'company_logo' => 'logos/company.png',
            'currency' => 'IQD',
        ]);

        $logo = app(CompanyBrandService::class)->logoDataUri($company->id);

        $this->assertStringStartsWith('data:', $logo);
        $this->assertStringContainsString(';base64,'.base64_encode('test-image-contents'), $logo);
    }
}
