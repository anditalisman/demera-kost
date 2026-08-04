<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutOtherDevicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_out_other_devices_with_correct_password(): void
    {
        $user = User::factory()->create();
        $originalHash = $user->password;

        $response = $this->actingAs($user)->delete('/other-browser-sessions', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertNotSame($originalHash, $user->fresh()->password);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_logout_other_devices_requires_correct_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/other-browser-sessions', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
