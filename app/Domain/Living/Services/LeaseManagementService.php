<?php

namespace App\Domain\Living\Services;

use App\Domain\Living\Exceptions\InvalidMoveInDateException;
use App\Domain\Living\Exceptions\RoomNotAvailableException;
use App\Domain\Living\Models\Deposit;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\LeaseExtension;
use App\Domain\Living\Models\Room;
use App\Enums\DepositStatus;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Lease operations an admin performs outside the automatic booking→payment
 * flow: confirming the tenant's actual move-in date (see confirmMoveIn()),
 * extending a contract, transferring a tenant to a different room, and
 * ending a lease with deposit settlement.
 */
class LeaseManagementService
{
    /**
     * A tenant's contract only starts counting once they actually move in — up to 7
     * days after payment is confirmed (BookingLifecycleService::confirm() already
     * defaults start_date to that deadline). Calling this before the tenant shows up
     * pulls the lease's billing start back to the real, earlier move-in date; the
     * default stands untouched if this is never called.
     */
    public function confirmMoveIn(Lease $lease, User $admin, ?string $moveInDate = null): Lease
    {
        return DB::transaction(function () use ($lease, $moveInDate) {
            $locked = Lease::query()->lockForUpdate()->findOrFail($lease->id);

            if ($locked->moved_in_confirmed_at !== null) {
                throw new InvalidMoveInDateException('Tanggal menempati sudah pernah dikonfirmasi untuk kontrak ini.');
            }

            $paidAt = ($locked->booking?->confirmed_at ?? $locked->created_at)->copy()->startOfDay();
            $deadline = $paidAt->copy()->addDays(7);
            $date = $moveInDate ? Carbon::parse($moveInDate)->startOfDay() : now()->startOfDay();

            if ($date->lt($paidAt)) {
                throw new InvalidMoveInDateException('Tanggal menempati tidak boleh sebelum tanggal pembayaran dikonfirmasi.');
            }

            if ($date->gt($deadline)) {
                throw new InvalidMoveInDateException('Tanggal menempati sudah melewati batas 7 hari sejak pembayaran — kontrak sudah otomatis dimulai per tanggal batas tersebut.');
            }

            $locked->update([
                'start_date' => $date,
                'end_date' => $date->copy()->addMonthsNoOverflow($locked->duration_months),
                'billing_cycle_day' => min($date->day, 28),
                'moved_in_confirmed_at' => now(),
            ]);

            $locked->tenant?->update(['joined_at' => $date]);

            return $locked->fresh();
        });
    }

    public function extend(Lease $lease, int $additionalMonths, ?string $newMonthlyPrice, User $admin, ?string $notes = null): LeaseExtension
    {
        return DB::transaction(function () use ($lease, $additionalMonths, $newMonthlyPrice, $admin, $notes) {
            $locked = Lease::query()->lockForUpdate()->findOrFail($lease->id);

            $previousEnd = $locked->end_date;
            $newEnd = $previousEnd->copy()->addMonthsNoOverflow($additionalMonths);
            $price = $newMonthlyPrice ?? (string) $locked->monthly_price;

            $extension = LeaseExtension::create([
                'lease_id' => $locked->id,
                'previous_end_date' => $previousEnd,
                'new_end_date' => $newEnd,
                'duration_months' => $additionalMonths,
                'price_at_extension' => $price,
                'status' => 'approved',
                'requested_by' => $admin->id,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'notes' => $notes,
            ]);

            $locked->update([
                'end_date' => $newEnd,
                'monthly_price' => $price,
                'duration_months' => $locked->duration_months + $additionalMonths,
            ]);

            return $extension;
        });
    }

    public function transferRoom(Lease $lease, Room $newRoom, User $admin, ?string $reason = null): Lease
    {
        return DB::transaction(function () use ($lease, $newRoom, $admin, $reason) {
            $locked = Lease::query()->lockForUpdate()->findOrFail($lease->id);
            $lockedNewRoom = Room::query()->lockForUpdate()->findOrFail($newRoom->id);

            if ($lockedNewRoom->status !== RoomStatus::Available || ! $lockedNewRoom->is_active) {
                throw new RoomNotAvailableException('Kamar tujuan sudah tidak tersedia.');
            }

            $oldRoom = Room::query()->lockForUpdate()->findOrFail($locked->room_id);

            $locked->update([
                'status' => LeaseStatus::Completed,
                'end_date' => now()->toDateString(),
            ]);

            $oldRoom->recordStatusChange(RoomStatus::Available, $reason ?? "Penyewa pindah ke kamar {$lockedNewRoom->room_number}", $admin->id);

            $newLease = Lease::create([
                'lease_number' => $this->generateLeaseNumber(),
                'tenant_id' => $locked->tenant_id,
                'room_id' => $lockedNewRoom->id,
                'booking_id' => null,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonthsNoOverflow($locked->duration_months)->toDateString(),
                'duration_months' => $locked->duration_months,
                'monthly_price' => $lockedNewRoom->monthly_price,
                'deposit_amount' => $lockedNewRoom->deposit_amount,
                'billing_cycle_day' => $locked->billing_cycle_day,
                'status' => LeaseStatus::Active,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            $lockedNewRoom->recordStatusChange(RoomStatus::Occupied, "Kamar terisi melalui perpindahan dari kamar {$oldRoom->room_number}", $admin->id);

            $locked->tenant->update(['room_id' => $lockedNewRoom->id]);

            return $newLease;
        });
    }

    public function terminate(Lease $lease, User $admin, ?string $reason, string $depositReturnedAmount, ?string $deductionNotes = null): Lease
    {
        return DB::transaction(function () use ($lease, $admin, $reason, $depositReturnedAmount, $deductionNotes) {
            $locked = Lease::query()->lockForUpdate()->findOrFail($lease->id);
            $isEarly = $locked->end_date->isFuture();

            $locked->update([
                'status' => LeaseStatus::Completed,
                'end_date' => $isEarly ? now()->toDateString() : $locked->end_date,
                'cancelled_at' => $isEarly ? now() : null,
                'cancellation_reason' => $isEarly ? $reason : null,
            ]);

            $room = Room::query()->lockForUpdate()->find($locked->room_id);

            if ($room) {
                $room->recordStatusChange(RoomStatus::Available, $reason ?? 'Sewa diakhiri', $admin->id);
            }

            $tenant = $locked->tenant;
            $tenant->update(['status' => TenantStatus::Inactive, 'moved_out_at' => now(), 'room_id' => null]);

            $deposit = Deposit::query()->where('lease_id', $locked->id)->latest('id')->first();

            if ($deposit) {
                $deposit->update([
                    'status' => bccomp($depositReturnedAmount, (string) $deposit->amount, 2) >= 0 ? DepositStatus::Returned : DepositStatus::PartiallyReturned,
                    'returned_amount' => $depositReturnedAmount,
                    'returned_at' => now()->toDateString(),
                    'deduction_notes' => $deductionNotes,
                ]);
            }

            return $locked->fresh();
        });
    }

    private function generateLeaseNumber(): string
    {
        do {
            $number = sprintf('LSE-%s-%s', now()->format('ymd'), Str::upper(Str::random(5)));
        } while (Lease::withTrashed()->where('lease_number', $number)->exists());

        return $number;
    }
}
