<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PersistentLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_is_remembered_on_the_device(): void
    {
        $user = User::factory()->create([
            'company_id' => null,
            'role' => 'super_admin',
            'email' => 'remember@example.test',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'remember@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $response->assertCookie(Auth::guard()->getRecallerName());
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_logged_in_user_is_not_shown_the_login_page_again(): void
    {
        $user = User::factory()->create(['company_id' => null, 'role' => 'super_admin']);

        $this->actingAs($user)->get('/login')->assertRedirect('/dashboard');
        $this->get('/')->assertRedirect('/dashboard');
    }
}
