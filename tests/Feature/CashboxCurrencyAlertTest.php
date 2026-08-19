<?php

namespace Tests\Feature;

use App\Models\Cashbox;
use App\Models\Company;
use App\Models\Setting;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\SystemAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CashboxCurrencyAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashbox_alert_uses_the_threshold_for_each_company_currency(): void
    {
        $iqdUser = $this->companyUser('IQD', 99999);
        $usdUser = $this->companyUser('USD', 4999);
        $iqdAtLimit = $this->companyUser('IQD', 100000);
        $usdAtLimit = $this->companyUser('USD', 5000);

        $service = app(SystemAlertService::class);
        foreach ([$iqdUser, $usdUser, $iqdAtLimit, $usdAtLimit] as $user) {
            $service->ensureFor($user);
        }

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $iqdUser->id,
            'kind' => 'low_balance',
        ]);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $usdUser->id,
            'kind' => 'low_balance',
        ]);
        $this->assertStringContainsString('100,000', SystemNotification::where('user_id', $iqdUser->id)->where('kind', 'low_balance')->value('message'));
        $this->assertStringContainsString('5,000', SystemNotification::where('user_id', $usdUser->id)->where('kind', 'low_balance')->value('message'));
        $this->assertDatabaseMissing('system_notifications', ['user_id' => $iqdAtLimit->id, 'kind' => 'low_balance']);
        $this->assertDatabaseMissing('system_notifications', ['user_id' => $usdAtLimit->id, 'kind' => 'low_balance']);
    }

    private function companyUser(string $currency, float $balance): User
    {
        $company = Company::create([
            'name' => $currency.' Alert Company',
            'code' => $currency.Str::upper(Str::random(8)),
            'status' => 'active',
            'subscription_start' => now()->subDay(),
            'subscription_end' => now()->addYear(),
        ]);
        Setting::create(['company_id' => $company->id, 'company_name' => $company->name, 'currency' => $currency]);
        Cashbox::create(['company_id' => $company->id, 'balance' => $balance]);

        return User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
    }
}
