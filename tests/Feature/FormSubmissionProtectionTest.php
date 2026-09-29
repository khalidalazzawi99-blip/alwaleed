<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\PartyDebtTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_contains_global_enter_and_double_submit_protection(): void
    {
        $company = Company::create(['name' => 'Test', 'code' => 'FORM-TEST', 'status' => 'active']);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);

        $this->actingAs($user)->get('/customers')->assertOk()
            ->assertSee("event.key !== 'Enter'", false)
            ->assertSee('event.shiftKey', false)
            ->assertSee('form.dataset.submitting', false);
    }

    public function test_identical_backend_submission_is_created_only_once(): void
    {
        $company = Company::create(['name' => 'Test', 'code' => 'FORM-DUPE', 'status' => 'active']);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'Customer']);
        $payload = ['_submission_token' => 'same-submission-token-123456', 'party_type' => 'customer', 'party_id' => $customer->id, 'type' => 'borrowing', 'transaction_date' => '2026-09-29', 'amount' => 100, 'notes' => 'once'];

        $this->actingAs($user)->post('/party-debt-transactions', $payload)->assertRedirect();
        $this->post('/party-debt-transactions', $payload)->assertRedirect();

        $this->assertSame(1, PartyDebtTransaction::count());
    }
}
