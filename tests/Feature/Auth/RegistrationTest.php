<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'whatsapp_number' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertTrue($this->app['auth']->user()->hasRole('customer'));
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'whatsapp_number' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('terms');
        $this->assertGuest();
    }

    public function test_registration_requires_unique_whatsapp_number(): void
    {
        $this->seed(RolePermissionSeeder::class);

        \App\Models\User::factory()->create(['whatsapp_number' => '081234567890']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'unique@example.com',
            'whatsapp_number' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ]);

        $response->assertSessionHasErrors('whatsapp_number');
    }
}
