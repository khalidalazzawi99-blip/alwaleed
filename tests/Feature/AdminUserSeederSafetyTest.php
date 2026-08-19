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

    private const INITIAL_ADMIN_VARIABLES = [
        'INITIAL_ADMIN_NAME',
        'INITIAL_ADMIN_EMAIL',
        'INITIAL_ADMIN_PASSWORD',
    ];

    protected function tearDown(): void
    {
        foreach (self::INITIAL_ADMIN_VARIABLES as $variable) {
            putenv($variable);
            unset($_ENV[$variable], $_SERVER[$variable]);
        }

        parent::tearDown();
    }

    public function test_seeder_preserves_an_existing_admin_password_and_credentials(): void
    {
        $this->setInitialAdminEnvironment(
            'Replacement Name',
            'admin@alwaleed.com',
            'replacement-test-password',
        );

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

    public function test_seeder_does_nothing_when_initial_admin_password_is_missing(): void
    {
        $this->setInitialAdminEnvironment('Initial Admin', 'new-admin@example.test', null);

        app(AdminUserSeeder::class)->run();

        $this->assertDatabaseMissing('users', ['email' => 'new-admin@example.test']);
    }

    public function test_seeder_creates_the_initial_admin_when_all_variables_are_present(): void
    {
        $this->setInitialAdminEnvironment(
            'Initial Admin',
            'new-admin@example.test',
            'temporary-test-password',
        );

        app(AdminUserSeeder::class)->run();

        $user = User::where('email', 'new-admin@example.test')->sole();

        $this->assertSame('Initial Admin', $user->name);
        $this->assertSame('super_admin', $user->role);
        $this->assertTrue(Hash::check('temporary-test-password', $user->password));
    }

    private function setInitialAdminEnvironment(string $name, string $email, ?string $password): void
    {
        $values = [
            'INITIAL_ADMIN_NAME' => $name,
            'INITIAL_ADMIN_EMAIL' => $email,
            'INITIAL_ADMIN_PASSWORD' => $password,
        ];

        foreach ($values as $variable => $value) {
            if ($value === null) {
                putenv($variable);
                unset($_ENV[$variable], $_SERVER[$variable]);
                continue;
            }

            putenv("{$variable}={$value}");
            $_ENV[$variable] = $value;
            $_SERVER[$variable] = $value;
        }
    }
}
