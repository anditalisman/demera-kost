<?php

namespace Tests\Feature\Platform;

use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\Tenant;
use App\Domain\Platform\Models\Notification;
use App\Domain\Platform\Models\NotificationLog;
use App\Domain\Platform\Services\NotificationDispatcher;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Enums\TenantStatus;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationDispatcherTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('customer');

        return $user;
    }

    public function test_dispatch_creates_in_app_notification_and_channel_log(): void
    {
        $user = $this->user();

        $notification = app(NotificationDispatcher::class)->dispatch($user, 'payment_verified', [
            'payment_code' => 'PAY-TEST-1',
            'amount' => '1.850.000',
        ]);

        $this->assertNotNull($notification);
        $this->assertStringContainsString('PAY-TEST-1', $notification->body);
        $this->assertSame($user->id, $notification->user_id);

        $inAppLog = NotificationLog::where('notification_id', $notification->id)->where('channel', NotificationChannel::InApp)->first();
        $this->assertNotNull($inAppLog);
        $this->assertSame(NotificationDeliveryStatus::Sent, $inAppLog->status);
    }

    public function test_dispatch_for_a_whatsapp_template_also_writes_a_whatsapp_log(): void
    {
        $user = $this->user();

        $notification = app(NotificationDispatcher::class)->dispatch($user, 'booking_confirmed_wa', [
            'name' => $user->name,
            'room_name' => 'Kamar A101',
            'booking_code' => 'BK-TEST-1',
        ]);

        $waLog = NotificationLog::where('notification_id', $notification->id)->where('channel', NotificationChannel::Whatsapp)->first();
        $this->assertNotNull($waLog);
        $this->assertSame(NotificationDeliveryStatus::Sent, $waLog->status);
        $this->assertSame('log', $waLog->provider);
        $this->assertStringContainsString('belum ada provider', mb_strtolower($waLog->provider_response));
    }

    public function test_dispatch_returns_null_for_unknown_template(): void
    {
        $user = $this->user();

        $notification = app(NotificationDispatcher::class)->dispatch($user, 'does_not_exist', []);

        $this->assertNull($notification);
    }

    public function test_retry_failed_advances_attempts_and_marks_sent(): void
    {
        $user = $this->user();
        $notification = Notification::create(['user_id' => $user->id, 'type' => 'x', 'title' => 'x', 'body' => 'x']);
        $log = NotificationLog::create([
            'notification_id' => $notification->id,
            'user_id' => $user->id,
            'channel' => NotificationChannel::Whatsapp,
            'status' => NotificationDeliveryStatus::Failed,
            'attempts' => 1,
        ]);

        $retried = app(NotificationDispatcher::class)->retryFailed();

        $this->assertSame(1, $retried);
        $this->assertSame(NotificationDeliveryStatus::Sent, $log->fresh()->status);
        $this->assertSame(2, $log->fresh()->attempts);
    }

    public function test_send_due_reminders_sends_h7_reminder_and_is_idempotent(): void
    {
        $user = $this->user();
        $room = Room::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id, 'status' => TenantStatus::Active, 'room_id' => $room->id]);
        $lease = Lease::factory()->create(['tenant_id' => $tenant->id, 'room_id' => $room->id, 'status' => LeaseStatus::Active]);

        Invoice::factory()->create([
            'lease_id' => $lease->id,
            'tenant_id' => $tenant->id,
            'status' => InvoiceStatus::Unpaid,
            'due_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 1500000,
        ]);

        $this->artisan('notifications:send-due-reminders')->assertSuccessful();
        $this->assertSame(1, Notification::where('type', 'invoice_due_reminder_h7')->count());

        $this->artisan('notifications:send-due-reminders')->assertSuccessful();
        $this->assertSame(1, Notification::where('type', 'invoice_due_reminder_h7')->count());
    }

    public function test_send_due_reminders_notifies_lease_ending_within_30_days(): void
    {
        $user = $this->user();
        $room = Room::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id, 'status' => TenantStatus::Active, 'room_id' => $room->id]);
        Lease::factory()->create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => LeaseStatus::Active,
            'end_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->artisan('notifications:send-due-reminders')->assertSuccessful();

        $this->assertSame(1, Notification::where('type', 'lease_expiring_soon')->count());
    }

    public function test_authenticated_user_can_view_and_mark_notifications_read(): void
    {
        $user = $this->user();
        $notification = Notification::create(['user_id' => $user->id, 'type' => 'x', 'title' => 'Halo', 'body' => 'Isi notifikasi']);

        $this->actingAs($user)->get('/notifications')->assertOk();

        $this->actingAs($user)->put("/notifications/{$notification->id}/read")->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }
}
