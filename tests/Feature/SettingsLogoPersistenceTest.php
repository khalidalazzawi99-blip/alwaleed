<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsLogoPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_logo_is_stored_permanently_and_preserved_by_later_settings_updates(): void
    {
        Storage::fake('public');
        $company = Company::create([
            'name' => 'Logo Company',
            'code' => 'LOGO-PERSIST',
            'status' => 'active',
            'subscription_start' => now()->subDay(),
            'subscription_end' => now()->addMonth(),
        ]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

        $this->actingAs($user)->post('/settings', $this->settingsData([
            'company_logo' => UploadedFile::fake()->image('brand.png', 320, 180),
        ]))->assertRedirect('/settings');

        $setting = Setting::where('company_id', $company->id)->firstOrFail();
        $logoPath = $setting->company_logo;
        Storage::disk('public')->assertExists($logoPath);
        $this->assertSame($logoPath, $company->fresh()->logo);

        $this->actingAs($user)->post('/settings', $this->settingsData([
            'phone' => '07700000000',
        ]))->assertRedirect('/settings');

        $this->assertSame($logoPath, $setting->fresh()->company_logo);
        $this->assertSame($logoPath, $company->fresh()->logo);
        Storage::disk('public')->assertExists($logoPath);
        $this->actingAs($user)->get('/settings')->assertOk()->assertSee('data:image/png;base64,', false);
    }

    private function settingsData(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'Logo Company',
            'phone' => null,
            'email' => 'logo@example.test',
            'address' => 'Baghdad',
            'currency' => 'IQD',
        ], $overrides);
    }
}
