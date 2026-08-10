<?php

namespace Tests\Feature\Living;

use App\Domain\Living\Exceptions\InvalidMoveInDateException;
use App\Domain\Living\Exceptions\RoomNotAvailableException;
use App\Domain\Living\Models\Booking;
use App\Domain\Living\Models\Deposit;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\Tenant;
use App\Domain\Living\Services\LeaseManagementService;
use App\Enums\DepositStatus;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaseManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function activeLeaseFor(Room $room): Lease
    {
        $tenant = Tenant::factory()->create(['status' => TenantStatus::Active, 'room_id' => $room->id]);

        return Lease::factory()->create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => LeaseStatus::Active,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(5)->toDateString(),
            'duration_months' => 6,
            'monthly_price' => 1500000,
            'deposit_amount' => 500000,
        ]);
    }

    public function test_extending_a_lease_pushes_out_the_end_date_and_records_extension(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $lease = $this->activeLeaseFor($room);
        $originalEnd = $lease->end_date->copy();

        $extension = app(LeaseManagementService::class)->extend($lease, 3, '1600000', $admin, 'Perpanjangan atas permintaan penyewa');

        $this->assertSame($originalEnd->toDateString(), $extension->previous_end_date->toDateString());
        $this->assertSame($originalEnd->copy()->addMonths(3)->toDateString(), $extension->new_end_date->toDateString());
        $this->assertSame('1600000.00', $lease->fresh()->monthly_price);
        $this->assertSame($originalEnd->copy()->addMonths(3)->toDateString(), $lease->fresh()->end_date->toDateString());
    }

    public function test_transferring_room_completes_old_lease_and_activates_new_one(): void
    {
        $admin = $this->admin();
        $oldRoom = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $newRoom = Room::factory()->create(['status' => RoomStatus::Available, 'monthly_price' => 2000000, 'deposit_amount' => 700000]);
        $lease = $this->activeLeaseFor($oldRoom);

        $newLease = app(LeaseManagementService::class)->transferRoom($lease, $newRoom, $admin, 'Pindah ke kamar lebih besar');

        $this->assertSame(LeaseStatus::Completed, $lease->fresh()->status);
        $this->assertSame(RoomStatus::Available, $oldRoom->fresh()->status);
        $this->assertSame(LeaseStatus::Active, $newLease->status);
        $this->assertSame(RoomStatus::Occupied, $newRoom->fresh()->status);
        $this->assertSame('2000000.00', $newLease->monthly_price);
        $this->assertSame($lease->tenant_id, $newLease->tenant_id);
        $this->assertSame($newRoom->id, $lease->tenant->fresh()->room_id);
    }

    public function test_transfer_is_rejected_when_target_room_is_unavailable(): void
    {
        $admin = $this->admin();
        $oldRoom = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $newRoom = Room::factory()->create(['status' => RoomStatus::Maintenance]);
        $lease = $this->activeLeaseFor($oldRoom);

        $this->expectException(RoomNotAvailableException::class);
        app(LeaseManagementService::class)->transferRoom($lease, $newRoom, $admin);
    }

    public function test_terminating_a_lease_frees_the_room_and_settles_full_deposit(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $lease = $this->activeLeaseFor($room);
        Deposit::factory()->create(['tenant_id' => $lease->tenant_id, 'lease_id' => $lease->id, 'amount' => 500000]);

        $terminated = app(LeaseManagementService::class)->terminate($lease, $admin, 'Selesai kontrak', '500000');

        $this->assertSame(LeaseStatus::Completed, $terminated->status);
        $this->assertSame(RoomStatus::Available, $room->fresh()->status);
        $this->assertSame(TenantStatus::Inactive, $lease->tenant->fresh()->status);
        $this->assertNull($lease->tenant->fresh()->room_id);

        $deposit = $lease->deposits()->latest('id')->first();
        $this->assertSame(DepositStatus::Returned, $deposit->status);
        $this->assertSame('500000.00', $deposit->returned_amount);
    }

    public function test_terminating_early_marks_partial_deposit_return_and_cancellation_reason(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $lease = $this->activeLeaseFor($room);
        Deposit::factory()->create(['tenant_id' => $lease->tenant_id, 'lease_id' => $lease->id, 'amount' => 500000]);

        $terminated = app(LeaseManagementService::class)->terminate($lease, $admin, 'Pelanggaran peraturan', '200000', 'Potongan kerusakan dinding');

        $this->assertNotNull($terminated->cancelled_at);
        $this->assertSame('Pelanggaran peraturan', $terminated->cancellation_reason);

        $deposit = $lease->deposits()->latest('id')->first();
        $this->assertSame(DepositStatus::PartiallyReturned, $deposit->status);
        $this->assertSame('Potongan kerusakan dinding', $deposit->deduction_notes);
    }

    /**
     * A lease still awaiting move-in confirmation: booking.confirmed_at pins the
     * "payment date" the 7-day grace window is measured from, and start_date sits
     * at that default deadline (mirroring what BookingLifecycleService::confirm()
     * actually produces) until confirmMoveIn() pulls it earlier.
     */
    private function pendingMoveInLeaseFor(Room $room, Carbon $paidAt): Lease
    {
        $booking = Booking::factory()->create(['room_id' => $room->id, 'confirmed_at' => $paidAt]);
        $tenant = Tenant::factory()->create(['status' => TenantStatus::Active, 'room_id' => $room->id, 'booking_id' => $booking->id]);
        $deadline = $paidAt->copy()->addDays(7)->startOfDay();

        return Lease::factory()->create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'booking_id' => $booking->id,
            'status' => LeaseStatus::Active,
            'start_date' => $deadline->toDateString(),
            'end_date' => $deadline->copy()->addMonths(6)->toDateString(),
            'duration_months' => 6,
            'moved_in_confirmed_at' => null,
        ]);
    }

    public function test_confirming_move_in_on_payment_day_starts_the_lease_that_day(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $paidAt = now()->subDays(3);
        $lease = $this->pendingMoveInLeaseFor($room, $paidAt);

        $updated = app(LeaseManagementService::class)->confirmMoveIn($lease, $admin, $paidAt->toDateString());

        $this->assertSame($paidAt->toDateString(), $updated->start_date->toDateString());
        $this->assertSame($paidAt->copy()->addMonthsNoOverflow(6)->toDateString(), $updated->end_date->toDateString());
        $this->assertNotNull($updated->moved_in_confirmed_at);
        $this->assertSame($paidAt->toDateString(), $updated->tenant->fresh()->joined_at->toDateString());
    }

    public function test_confirming_move_in_within_the_grace_window_updates_start_date(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $paidAt = now()->subDays(3);
        $lease = $this->pendingMoveInLeaseFor($room, $paidAt);
        $actualMoveIn = $paidAt->copy()->addDays(2);

        $updated = app(LeaseManagementService::class)->confirmMoveIn($lease, $admin, $actualMoveIn->toDateString());

        $this->assertSame($actualMoveIn->toDateString(), $updated->start_date->toDateString());
    }

    public function test_confirming_move_in_before_payment_date_is_rejected(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $paidAt = now()->subDays(3);
        $lease = $this->pendingMoveInLeaseFor($room, $paidAt);

        $this->expectException(InvalidMoveInDateException::class);
        app(LeaseManagementService::class)->confirmMoveIn($lease, $admin, $paidAt->copy()->subDay()->toDateString());
    }

    public function test_confirming_move_in_past_the_seven_day_deadline_is_rejected(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $paidAt = now()->subDays(10);
        $lease = $this->pendingMoveInLeaseFor($room, $paidAt);

        $this->expectException(InvalidMoveInDateException::class);
        app(LeaseManagementService::class)->confirmMoveIn($lease, $admin, now()->toDateString());
    }

    public function test_confirming_move_in_twice_is_rejected(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $paidAt = now()->subDays(3);
        $lease = $this->pendingMoveInLeaseFor($room, $paidAt);

        app(LeaseManagementService::class)->confirmMoveIn($lease->fresh(), $admin, $paidAt->toDateString());

        $this->expectException(InvalidMoveInDateException::class);
        app(LeaseManagementService::class)->confirmMoveIn($lease->fresh(), $admin, $paidAt->toDateString());
    }

    public function test_admin_can_confirm_move_in_via_http(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $paidAt = now()->subDays(3);
        $lease = $this->pendingMoveInLeaseFor($room, $paidAt);

        $this->actingAs($admin)->post("/admin/leases/{$lease->id}/confirm-move-in", [
            'move_in_date' => $paidAt->toDateString(),
        ])->assertRedirect();

        $this->assertSame($paidAt->toDateString(), $lease->fresh()->start_date->toDateString());
        $this->assertNotNull($lease->fresh()->moved_in_confirmed_at);
    }

    public function test_admin_can_extend_lease_via_http(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Occupied]);
        $lease = $this->activeLeaseFor($room);

        $this->actingAs($admin)->post("/admin/leases/{$lease->id}/extend", [
            'additional_months' => 2,
        ])->assertRedirect();

        $this->assertSame(8, $lease->fresh()->duration_months);
    }
}
