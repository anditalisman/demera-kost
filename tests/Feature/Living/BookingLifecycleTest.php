<?php

namespace Tests\Feature\Living;

use App\Domain\Living\Exceptions\RoomNotAvailableException;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Services\BookingLifecycleService;
use App\Domain\Platform\Models\ApplicationSetting;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('customer');

        return $user;
    }

    private function bookingData(): array
    {
        return [
            'start_date' => now()->addDay()->toDateString(),
            'duration_months' => 3,
            'notes' => null,
            'guests' => [
                ['full_name' => 'Budi Santoso', 'identity_number' => '3201xxxx', 'phone' => '08123456789', 'email' => 'budi@example.com'],
            ],
        ];
    }

    public function test_creating_a_hold_reserves_the_room_and_generates_an_invoice(): void
    {
        Storage::fake('private_documents');

        $user = $this->customer();
        $room = Room::factory()->create(['status' => RoomStatus::Available, 'monthly_price' => 1500000, 'deposit_amount' => 500000]);

        $booking = app(BookingLifecycleService::class)->createHold($user, $room, $this->bookingData());

        $this->assertSame(BookingStatus::AwaitingPayment, $booking->status);
        $this->assertNotEmpty($booking->booking_code);
        $this->assertSame('2000000.00', $booking->total_amount);
        $this->assertSame(RoomStatus::Held, $room->fresh()->status);
        $this->assertCount(1, $booking->guests);
        $this->assertTrue($booking->guests->first()->is_primary);

        $invoice = $booking->invoices->first();
        $this->assertSame(InvoiceStatus::Unpaid, $invoice->status);
        $this->assertSame('2000000.00', $invoice->total_amount);
        $this->assertCount(2, $invoice->items);
    }

    public function test_second_hold_attempt_on_the_same_room_is_rejected(): void
    {
        $user = $this->customer();
        $otherUser = $this->customer();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);

        app(BookingLifecycleService::class)->createHold($user, $room, $this->bookingData());

        $this->expectException(RoomNotAvailableException::class);
        app(BookingLifecycleService::class)->createHold($otherUser, $room->fresh(), $this->bookingData());
    }

    public function test_expiring_a_booking_releases_the_room_and_cancels_its_invoice(): void
    {
        $user = $this->customer();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);

        ApplicationSetting::set('booking_hold_hours', '1', ['type' => 'number', 'group' => 'booking', 'label' => 'x']);

        $booking = app(BookingLifecycleService::class)->createHold($user, $room, $this->bookingData());
        $booking->forceFill(['payment_due_at' => now()->subHour()])->save();

        app(BookingLifecycleService::class)->expire($booking);

        $this->assertSame(BookingStatus::Expired, $booking->fresh()->status);
        $this->assertSame(RoomStatus::Available, $room->fresh()->status);
        $this->assertSame(InvoiceStatus::Cancelled, $booking->invoices->first()->fresh()->status);
    }

    public function test_expire_command_only_processes_overdue_bookings(): void
    {
        $user = $this->customer();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);

        $booking = app(BookingLifecycleService::class)->createHold($user, $room, $this->bookingData());
        $booking->forceFill(['payment_due_at' => now()->subMinute()])->save();

        $this->artisan('bookings:expire')->assertSuccessful();

        $this->assertSame(BookingStatus::Expired, $booking->fresh()->status);
    }

    public function test_confirming_a_booking_creates_tenant_lease_and_deposit(): void
    {
        $user = $this->customer();
        $room = Room::factory()->create(['status' => RoomStatus::Available, 'monthly_price' => 1200000, 'deposit_amount' => 400000]);

        $booking = app(BookingLifecycleService::class)->createHold($user, $room, $this->bookingData());

        $lease = app(BookingLifecycleService::class)->confirm($booking->fresh());

        $this->assertSame(LeaseStatus::Active, $lease->status);
        $this->assertSame('1200000.00', $lease->monthly_price);
        $this->assertSame(RoomStatus::Occupied, $room->fresh()->status);
        $this->assertSame(BookingStatus::ConvertedToLease, $booking->fresh()->status);

        $tenant = $lease->tenant;
        $this->assertSame(TenantStatus::Active, $tenant->status);
        $this->assertSame($user->id, $tenant->user_id);

        $deposit = $tenant->deposits()->first();
        $this->assertSame('400000.00', $deposit->amount);
    }

    public function test_guest_is_redirected_to_login_when_trying_to_book(): void
    {
        $room = Room::factory()->create(['status' => RoomStatus::Available, 'is_active' => true]);

        $this->get("/living/rooms/{$room->slug}/book")->assertRedirect('/login');
    }

    public function test_authenticated_customer_can_submit_a_booking_via_http(): void
    {
        Storage::fake('private_documents');
        $user = $this->customer();
        $room = Room::factory()->create(['status' => RoomStatus::Available, 'is_active' => true]);

        $response = $this->actingAs($user)->post('/bookings', [
            'room_id' => $room->id,
            'start_date' => now()->addDay()->toDateString(),
            'duration_months' => 2,
            'guests' => [['full_name' => 'Siti Aminah']],
            'identity_document' => UploadedFile::fake()->create('ktp.jpg', 500, 'image/jpeg'),
            'terms_accepted' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['user_id' => $user->id, 'room_id' => $room->id]);
    }

    public function test_booking_confirmation_page_is_only_visible_to_owner_or_staff(): void
    {
        $owner = $this->customer();
        $stranger = $this->customer();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);

        $booking = app(BookingLifecycleService::class)->createHold($owner, $room, $this->bookingData());

        $this->actingAs($owner)->get("/bookings/{$booking->booking_code}")->assertOk();
        $this->actingAs($stranger)->get("/bookings/{$booking->booking_code}")->assertForbidden();
    }
}
