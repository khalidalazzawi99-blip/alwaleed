<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_preserves_an_existing_admin_password_and_credentials(): void
    {
        $user = User::factory()->create([
            'name' => 'Existing Production Admin',
            'email' => 'admin@alwaleed.com',
            'password' => Hash::make('existing-test-password'),
            'role' => 'admin',
        ]);
        $original = $user->only(['name', 'email', 'password', 'role', 'company_id']);

        app(AdminUserSeeder::class)->run();

        $this->assertSame($original, $user->fresh()->only(array_keys($original)));
        $this->assertDatabaseCount('users', 1);
    }
}
