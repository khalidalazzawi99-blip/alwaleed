<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartyEditingTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_user_can_edit_customer_and_supplier_details(): void
    {
        $company = Company::create([
            'name' => 'Edit Test', 'code' => 'EDIT', 'status' => 'active', 'max_users' => 5,
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth(),
        ]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'Old Customer']);
        $supplier = Supplier::create(['company_id' => $company->id, 'name' => 'Old Supplier']);

        $this->actingAs($user)->get("/customers/{$customer->id}/edit")->assertOk();
        $this->actingAs($user)->put("/customers/{$customer->id}", [
            'name' => 'New Customer', 'phone' => '100', 'company_name' => 'Customer Co',
            'address' => 'Baghdad', 'notes' => 'Updated',
        ])->assertRedirect('/customers');

        $this->actingAs($user)->get("/suppliers/{$supplier->id}/edit")->assertOk();
        $this->actingAs($user)->put("/suppliers/{$supplier->id}", [
            'name' => 'New Supplier', 'phone' => '200', 'company_name' => 'Supplier Co',
            'address' => 'Basra', 'notes' => 'Updated',
        ])->assertRedirect('/suppliers');

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'New Customer', 'phone' => '100']);
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'New Supplier', 'phone' => '200']);
    }

    public function test_user_cannot_edit_another_company_parties(): void
    {
        $first = Company::create([
            'name' => 'First', 'code' => 'FIRST', 'status' => 'active', 'max_users' => 5,
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth(),
        ]);
        $second = Company::create([
            'name' => 'Second', 'code' => 'SECOND', 'status' => 'active', 'max_users' => 5,
            'subscription_start' => now()->subDay(), 'subscription_end' => now()->addMonth(),
        ]);
        $user = User::factory()->create(['company_id' => $first->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $second->id, 'name' => 'Protected Customer']);
        $supplier = Supplier::create(['company_id' => $second->id, 'name' => 'Protected Supplier']);

        $this->actingAs($user)->get("/customers/{$customer->id}/edit")->assertForbidden();
        $this->actingAs($user)->put("/customers/{$customer->id}", ['name' => 'Changed'])->assertForbidden();
        $this->actingAs($user)->get("/suppliers/{$supplier->id}/edit")->assertForbidden();
        $this->actingAs($user)->put("/suppliers/{$supplier->id}", ['name' => 'Changed'])->assertForbidden();
    }
}
