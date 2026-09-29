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
        Storage::fake(config('filesystems.company_logo_disk'));
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
        Storage::disk(config('filesystems.company_logo_disk'))->assertExists($logoPath);
        $this->assertSame($logoPath, $company->fresh()->logo);

        $this->actingAs($user)->post('/settings', $this->settingsData([
            'phone' => '07700000000',
        ]))->assertRedirect('/settings');

        $this->assertSame($logoPath, $setting->fresh()->company_logo);
        $this->assertSame($logoPath, $company->fresh()->logo);
        Storage::disk(config('filesystems.company_logo_disk'))->assertExists($logoPath);
        $this->actingAs($user)->get('/settings')->assertOk()->assertSee('data:image/png;base64,', false);

        auth()->logout();
        $this->actingAs($user->fresh())->get('/settings')->assertOk()->assertSee('data:image/png;base64,', false);

        $this->actingAs($user)->post('/settings', $this->settingsData([
            'company_logo' => UploadedFile::fake()->image('replacement.png', 400, 200),
        ]))->assertRedirect('/settings');

        $replacementPath = $setting->fresh()->company_logo;
        $this->assertNotSame($logoPath, $replacementPath);
        Storage::disk(config('filesystems.company_logo_disk'))->assertMissing($logoPath);
        Storage::disk(config('filesystems.company_logo_disk'))->assertExists($replacementPath);

        $this->actingAs($user)->post('/settings', $this->settingsData(['remove_logo' => '1']))
            ->assertRedirect('/settings');
        $this->assertNull($setting->fresh()->company_logo);
        $this->assertNull($company->fresh()->logo);
        Storage::disk(config('filesystems.company_logo_disk'))->assertMissing($replacementPath);
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
