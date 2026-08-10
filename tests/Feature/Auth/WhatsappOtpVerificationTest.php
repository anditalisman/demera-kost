<?php

namespace Tests\Feature\Auth;

use App\Domain\Platform\Services\WhatsappOtpService;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WhatsappOtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);
        $user = User::factory()->unverified()->create([
            'whatsapp_number' => '081234567890',
            'whatsapp_verified_at' => null,
        ]);
        $user->assignRole('customer');

        return $user;
    }

    public function test_prompt_redirects_to_dashboard_once_whatsapp_verified(): void
    {
        $user = $this->user();
        $user->forceFill(['whatsapp_verified_at' => now()])->save();

        $this->actingAs($user)->get('/verify-whatsapp')
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_prompt_redirects_to_dashboard_when_only_email_is_verified(): void
    {
        $user = $this->user();
        $user->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($user)->get('/verify-whatsapp')
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_prompt_shows_the_form_when_neither_channel_is_verified(): void
    {
        $user = $this->user();

        $this->actingAs($user)->get('/verify-whatsapp')->assertOk();
    }

    public function test_correct_code_verifies_whatsapp_and_redirects_to_dashboard(): void
    {
        $user = $this->user();
        app(WhatsappOtpService::class)->generateAndSend($user);
        $code = Cache::get("whatsapp_otp:{$user->id}");

        $response = $this->actingAs($user)->post('/verify-whatsapp', ['code' => $code]);

        $response->assertRedirect(route('dashboard', absolute: false).'?whatsapp_verified=1');
        $this->assertNotNull($user->fresh()->whatsapp_verified_at);
        $this->assertNull(Cache::get("whatsapp_otp:{$user->id}"));
    }

    public function test_wrong_code_is_rejected(): void
    {
        $user = $this->user();
        app(WhatsappOtpService::class)->generateAndSend($user);

        $response = $this->actingAs($user)->post('/verify-whatsapp', ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->whatsapp_verified_at);
    }

    public function test_expired_code_is_rejected(): void
    {
        $user = $this->user();
        Cache::put("whatsapp_otp:{$user->id}", '123456', now()->subMinute());

        $response = $this->actingAs($user)->post('/verify-whatsapp', ['code' => '123456']);

        $response->assertSessionHasErrors('code');
    }

    public function test_resend_issues_a_new_code(): void
    {
        $user = $this->user();
        app(WhatsappOtpService::class)->generateAndSend($user);

        $this->actingAs($user)->post('/whatsapp/verification-notification')
            ->assertSessionHas('status', 'whatsapp-otp-sent');

        $this->assertNotNull(Cache::get("whatsapp_otp:{$user->id}"));
    }
}
