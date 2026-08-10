<?php

namespace Tests\Feature\Platform;

use App\Domain\Platform\Models\NotificationLog;
use App\Domain\Platform\Services\NotificationDispatcher;
use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenWaDriverTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);
        $user = User::factory()->create(['whatsapp_number' => '081234567890']);
        $user->assignRole('customer');

        return $user;
    }

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.whatsapp.provider' => 'openwa',
            'services.openwa.base_url' => 'https://openwa.test',
            'services.openwa.api_key' => 'test-key',
            'services.openwa.session_id' => 'demera',
        ]);
    }

    public function test_dispatch_sends_through_openwa_with_normalized_jid(): void
    {
        Http::fake([
            'openwa.test/*' => Http::response(['messageId' => 'true_628123@c.us_ABC', 'timestamp' => 1700000000], 201),
        ]);

        $user = $this->user();

        $notification = app(NotificationDispatcher::class)->dispatch($user, 'booking_confirmed_wa', [
            'name' => $user->name,
            'room_name' => 'Kamar A101',
            'booking_code' => 'BK-TEST-1',
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://openwa.test/api/sessions/demera/messages/send-text'
                && $request['chatId'] === '6281234567890@c.us'
                && $request->hasHeader('Authorization', 'Bearer test-key');
        });

        $waLog = NotificationLog::where('notification_id', $notification->id)->where('channel', NotificationChannel::Whatsapp)->first();
        $this->assertSame(NotificationDeliveryStatus::Sent, $waLog->status);
        $this->assertSame('openwa', $waLog->provider);
    }

    public function test_dispatch_marks_log_failed_when_openwa_returns_an_error(): void
    {
        Http::fake([
            'openwa.test/*' => Http::response(['message' => "Session 'demera' is not active. Start the session first."], 400),
        ]);

        $user = $this->user();

        $notification = app(NotificationDispatcher::class)->dispatch($user, 'booking_confirmed_wa', [
            'name' => $user->name,
            'room_name' => 'Kamar A101',
            'booking_code' => 'BK-TEST-1',
        ]);

        $waLog = NotificationLog::where('notification_id', $notification->id)->where('channel', NotificationChannel::Whatsapp)->first();
        $this->assertSame(NotificationDeliveryStatus::Failed, $waLog->status);
        $this->assertStringContainsString('not active', $waLog->error_message);
    }

    public function test_dispatch_marks_log_failed_for_a_whatsapp_number_with_no_digits(): void
    {
        Http::fake();

        $user = $this->user();
        $user->forceFill(['whatsapp_number' => 'n/a'])->saveQuietly();

        $notification = app(NotificationDispatcher::class)->dispatch($user, 'booking_confirmed_wa', [
            'name' => $user->name,
            'room_name' => 'Kamar A101',
            'booking_code' => 'BK-TEST-1',
        ]);

        $waLog = NotificationLog::where('notification_id', $notification->id)->where('channel', NotificationChannel::Whatsapp)->first();
        $this->assertSame(NotificationDeliveryStatus::Failed, $waLog->status);
        Http::assertNothingSent();
    }
}
