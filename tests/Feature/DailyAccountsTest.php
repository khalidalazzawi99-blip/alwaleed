<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\DailyExpense;
use App\Models\DailyExpenseParty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class DailyAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_forwarded_https_headers_are_trusted_behind_render_proxy(): void
    {
        Route::middleware('web')->get('/_proxy-scheme-test', fn () => request()->getScheme().'|'.url('/probe'));

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.10'])
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'secure.example.test',
                'X-Forwarded-Port' => '443',
            ])
            ->get('/_proxy-scheme-test')
            ->assertOk()
            ->assertSeeText('https|https://secure.example.test/probe');
    }

    public function test_daily_account_form_actions_are_relative_and_never_http(): void
    {
        [$sippar, $user] = $this->companyUser('SIPPAR', 'admin');
        $party = $this->party($sippar, 'خالد');
        $expense = $this->expense($sippar, $party, 100);

        $index = $this->actingAs($user)->get('/sippar/daily-accounts?year=2026&currency=IQD')->assertOk();
        $index->assertSee('action="/sippar/daily-accounts"', false)
            ->assertSee('action="/sippar/daily-accounts/parties"', false)
            ->assertSee('action="/sippar/daily-accounts/'.$expense->id.'"', false)
            ->assertDontSee('action="http://', false);

        $this->actingAs($user)
            ->get('/sippar/daily-accounts/'.$expense->id.'/edit')
            ->assertOk()
            ->assertSee('action="/sippar/daily-accounts/'.$expense->id.'"', false)
            ->assertDontSee('action="http://', false);
    }

    public function test_only_an_authorized_sippar_user_can_open_daily_accounts(): void
    {
        [$sippar, $user] = $this->companyUser('SIPPAR', 'admin');
        [$other, $otherUser] = $this->companyUser('KUDIA', 'admin');

        $this->actingAs($user)->get(route('sippar.daily-accounts.index'))->assertOk();
        $this->actingAs($otherUser)->get(route('sippar.daily-accounts.index'))->assertForbidden();

        $viewer = User::create([
            'company_id' => $sippar->id,
            'name' => 'Viewer',
            'email' => 'viewer@example.test',
            'password' => 'password',
            'role' => 'user',
        ]);
        $this->actingAs($viewer)->get(route('sippar.daily-accounts.index'))->assertForbidden();
    }

    public function test_expense_is_created_for_sippar_and_company_cannot_be_supplied_by_client(): void
    {
        [$sippar, $user] = $this->companyUser('SIPPAR', 'admin');
        [$other] = $this->companyUser('KUDIA', 'admin');
        $party = $this->party($sippar, 'ياسر');

        $this->actingAs($user)->post(route('sippar.daily-accounts.store'), [
            'expense_date' => '2026-06-03',
            'amount' => 500000,
            'currency' => 'IQD',
            'party_id' => $party->id,
            'notes' => 'مصاريف سلندر',
        ])->assertRedirect();

        $this->assertDatabaseHas('daily_expenses', [
            'company_id' => $sippar->id,
            'party_id' => $party->id,
            'created_by' => $user->id,
            'amount' => 500000,
        ]);

        $this->actingAs($user)->post(route('sippar.daily-accounts.store'), [
            'company_id' => $other->id,
            'expense_date' => '2026-06-04',
            'amount' => 1,
            'currency' => 'IQD',
            'party_id' => $party->id,
        ])->assertSessionHasErrors('company_id');
        $this->assertDatabaseCount('daily_expenses', 1);
    }

    public function test_update_and_delete_are_scoped_to_sippar(): void
    {
        [$sippar, $user] = $this->companyUser('SIPPAR', 'admin');
        [$other] = $this->companyUser('KUDIA', 'admin');
        $sipparParty = $this->party($sippar, 'خالد');
        $otherParty = $this->party($other, 'Private party');
        $sipparExpense = $this->expense($sippar, $sipparParty, 100);
        $otherExpense = $this->expense($other, $otherParty, 999999);

        $this->actingAs($user)->put(route('sippar.daily-accounts.update', $sipparExpense), [
            'expense_date' => '2026-06-10',
            'amount' => 250,
            'currency' => 'IQD',
            'party_id' => $sipparParty->id,
            'notes' => 'updated',
        ])->assertRedirect();
        $this->assertDatabaseHas('daily_expenses', ['id' => $sipparExpense->id, 'amount' => 250]);

        $payload = [
            'expense_date' => '2026-06-10', 'amount' => 1, 'currency' => 'IQD',
            'party_id' => $sipparParty->id,
        ];
        $this->actingAs($user)->put(route('sippar.daily-accounts.update', $otherExpense), $payload)->assertNotFound();
        $this->actingAs($user)->delete(route('sippar.daily-accounts.destroy', $otherExpense))->assertNotFound();
        $this->assertDatabaseHas('daily_expenses', ['id' => $otherExpense->id, 'amount' => 999999]);

        $this->actingAs($user)->delete(route('sippar.daily-accounts.destroy', $sipparExpense))->assertRedirect();
        $this->assertDatabaseMissing('daily_expenses', ['id' => $sipparExpense->id]);
    }

    public function test_filters_and_all_aggregates_use_the_same_scoped_result_set(): void
    {
        [$sippar, $user] = $this->companyUser('SIPPAR', 'accountant');
        [$other] = $this->companyUser('KUDIA', 'admin');
        $khalid = $this->party($sippar, 'خالد');
        $yasser = $this->party($sippar, 'ياسر');
        $private = $this->party($other, 'KUDIA secret');

        $this->expense($sippar, $khalid, 100, '2026-06-03', 'fuel');
        $this->expense($sippar, $khalid, 300, '2026-06-20', 'fuel');
        $this->expense($sippar, $yasser, 900, '2026-05-01', 'rent');
        $this->expense($sippar, $yasser, 700, '2025-06-01', 'old fuel');
        $this->expense($other, $private, 99999999, '2026-06-03', 'must never appear');

        $response = $this->actingAs($user)->get(route('sippar.daily-accounts.index', [
            'year' => 2026,
            'month' => 6,
            'party_id' => $khalid->id,
            'notes' => 'fuel',
            'min_amount' => 200,
            'max_amount' => 400,
            'currency' => 'IQD',
        ]))->assertOk();

        $summary = $response->viewData('summary');
        $this->assertEquals(300, $summary['total']);
        $this->assertSame(1, $summary['count']);
        $this->assertEquals(300, $summary['average']);
        $this->assertSame('خالد', $summary['top_person']->party->name);
        $this->assertSame(6, $summary['top_month']['month']);
        $this->assertSame('2026-06-20', $summary['last_date']);
        $this->assertCount(1, $summary['people']);
        $this->assertCount(12, $summary['months']);
        $this->assertEquals(300, $summary['months']->firstWhere('month', 6)['total']);
        $this->assertEquals(0, $summary['months']->firstWhere('month', 5)['total']);
        $this->assertSame(1, $response->viewData('expenses')->total());
        $response->assertDontSee('KUDIA secret')->assertDontSee('must never appear');
    }

    public function test_date_range_filter_is_applied(): void
    {
        [$sippar, $user] = $this->companyUser('SIPPAR', 'admin');
        $party = $this->party($sippar, 'أمير');
        $this->expense($sippar, $party, 10, '2026-01-01');
        $this->expense($sippar, $party, 20, '2026-01-15');
        $this->expense($sippar, $party, 30, '2026-01-31');

        $response = $this->actingAs($user)->get(route('sippar.daily-accounts.index', [
            'year' => 2026, 'from' => '2026-01-10', 'to' => '2026-01-20', 'currency' => 'IQD',
        ]))->assertOk();

        $this->assertEquals(20, $response->viewData('summary')['total']);
        $this->assertSame(1, $response->viewData('expenses')->total());
    }

    public function test_excel_export_contains_all_filtered_sippar_rows_and_no_other_company_data(): void
    {
        [$sippar, $user] = $this->companyUser('SIPPAR', 'admin');
        [$other] = $this->companyUser('KUDIA', 'admin');
        $sipparParty = $this->party($sippar, 'خالد');
        $otherParty = $this->party($other, 'KUDIA secret');
        foreach (range(1, 25) as $day) {
            $this->expense($sippar, $sipparParty, $day, sprintf('2026-06-%02d', $day), 'Sippar row '.$day);
        }
        $this->expense($other, $otherParty, 999999, '2026-06-01', 'KUDIA hidden row');

        $response = $this->actingAs($user)->get(route('sippar.daily-accounts.excel', [
            'year' => 2026, 'month' => 6, 'currency' => 'IQD',
        ]))->assertOk();
        $bytes = $response->streamedContent();
        $this->assertStringStartsWith('PK', $bytes);

        $path = tempnam(sys_get_temp_dir(), 'daily-accounts-');
        file_put_contents($path, $bytes);
        $workbook = IOFactory::load($path);
        unlink($path);

        $this->assertSame(4, $workbook->getSheetCount());
        $daily = $workbook->getSheet(0);
        $this->assertTrue($daily->getRightToLeft());
        $this->assertSame('أضواء سيبار', $daily->getCell('A1')->getValue());
        $this->assertSame(25 + 2, $daily->getHighestDataRow() - 5);
        $serialized = json_encode($workbook->getSheet(0)->toArray(), JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('KUDIA', $serialized);
        $this->assertStringNotContainsString('hidden row', $serialized);
    }

    public function test_pdf_export_contains_only_sippar_data(): void
    {
        [$sippar, $user] = $this->companyUser('SIPPAR', 'admin');
        [$other] = $this->companyUser('KUDIA', 'admin');
        $this->expense($sippar, $this->party($sippar, 'Sippar visible'), 123, '2026-06-03');
        $this->expense($other, $this->party($other, 'KUDIA secret'), 999, '2026-06-03');

        $response = $this->actingAs($user)->get(route('sippar.daily-accounts.pdf', [
            'year' => 2026, 'month' => 6, 'currency' => 'IQD',
        ]))->assertOk()->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringNotContainsString('KUDIA secret', $response->getContent());
    }

    public function test_foreign_company_party_is_rejected(): void
    {
        [$sippar, $user] = $this->companyUser('SIPPAR', 'admin');
        [$other] = $this->companyUser('KUDIA', 'admin');
        $foreignParty = $this->party($other, 'Foreign');

        $this->actingAs($user)->post(route('sippar.daily-accounts.store'), [
            'expense_date' => '2026-06-03',
            'amount' => 100,
            'currency' => 'IQD',
            'party_id' => $foreignParty->id,
        ])->assertSessionHasErrors('party_id');

        $this->assertDatabaseMissing('daily_expenses', ['company_id' => $sippar->id]);
    }

    private function companyUser(string $code, string $role): array
    {
        $company = Company::create([
            'name' => $code === 'SIPPAR' ? 'أضواء سيبار' : 'شركة '.$code,
            'code' => $code,
            'status' => 'active',
            'subscription_start' => now()->subDay(),
            'subscription_end' => now()->addMonth(),
            'max_users' => 10,
        ]);
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Test '.$code,
            'email' => strtolower($code).'-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => $role,
        ]);

        return [$company, $user];
    }

    private function party(Company $company, string $name): DailyExpenseParty
    {
        return $company->dailyExpenseParties()->create(['name' => $name]);
    }

    private function expense(
        Company $company,
        DailyExpenseParty $party,
        int|float $amount,
        string $date = '2026-06-03',
        ?string $notes = null,
    ): DailyExpense {
        return $company->dailyExpenses()->create([
            'expense_date' => $date,
            'amount' => $amount,
            'currency' => 'IQD',
            'party_id' => $party->id,
            'notes' => $notes,
        ]);
    }
}
