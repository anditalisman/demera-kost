<?php

namespace App\Domain\Living\Services;

use App\Domain\Living\Exceptions\RoomNotAvailableException;
use App\Domain\Living\Models\Booking;
use App\Domain\Living\Models\Deposit;
use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\Tenant;
use App\Domain\Platform\Models\ApplicationSetting;
use App\Domain\Platform\Services\NotificationDispatcher;
use App\Domain\Platform\Services\PrivateDocumentUploadService;
use App\Enums\BookingStatus;
use App\Enums\DepositStatus;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Owns every state transition a booking goes through: hold creation
 * (with row-locked double-booking prevention), automatic expiry, and
 * conversion into an active tenant + lease once payment is verified.
 */
class BookingLifecycleService
{
    public function __construct(
        private readonly PrivateDocumentUploadService $documentUploadService,
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    /**
     * @param  array{start_date: string, duration_months: int, notes?: ?string, guests: array<int, array{full_name: string, identity_number?: ?string, phone?: ?string, email?: ?string, relationship?: ?string}>, identity_document?: ?UploadedFile}  $data
     */
    public function createHold(User $user, Room $room, array $data): Booking
    {
        return DB::transaction(function () use ($user, $room, $data) {
            $lockedRoom = Room::query()->lockForUpdate()->findOrFail($room->id);

            if ($lockedRoom->status !== RoomStatus::Available || ! $lockedRoom->is_active) {
                throw new RoomNotAvailableException('Kamar ini sudah tidak tersedia untuk dipesan.');
            }

            $holdHours = (int) ApplicationSetting::get('booking_hold_hours', 24);
            $adminFee = (float) ApplicationSetting::get('booking_admin_fee', 0);
            $monthlyPrice = (float) $lockedRoom->monthly_price;
            $depositAmount = (float) $lockedRoom->deposit_amount;
            $totalAmount = $monthlyPrice + $depositAmount + $adminFee;

            $booking = Booking::create([
                'user_id' => $user->id,
                'room_id' => $lockedRoom->id,
                'booking_code' => $this->generateUniqueCode('BK', fn ($code) => Booking::withTrashed()->where('booking_code', $code)->exists()),
                'status' => BookingStatus::AwaitingPayment,
                'start_date' => $data['start_date'],
                'duration_months' => $data['duration_months'],
                'monthly_price' => $monthlyPrice,
                'deposit_amount' => $depositAmount,
                'admin_fee' => $adminFee,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_due_at' => now()->addHours($holdHours),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['guests'] as $index => $guestData) {
                $booking->guests()->create([
                    'full_name' => $guestData['full_name'],
                    'identity_number' => $guestData['identity_number'] ?? null,
                    'phone' => $guestData['phone'] ?? null,
                    'email' => $guestData['email'] ?? null,
                    'relationship' => $guestData['relationship'] ?? null,
                    'is_primary' => $index === 0,
                ]);
            }

            if (! empty($data['identity_document'])) {
                $uploaded = $this->documentUploadService->upload($data['identity_document'], 'booking-documents');

                $booking->documents()->create([
                    'document_type' => 'ktp',
                    'file_path' => $uploaded['path'],
                    'original_filename' => $uploaded['original_filename'],
                    'mime_type' => $uploaded['mime_type'],
                    'size_bytes' => $uploaded['size_bytes'],
                    'uploaded_by' => $user->id,
                ]);
            }

            $lockedRoom->recordStatusChange(RoomStatus::Held, "Ditahan untuk pemesanan {$booking->booking_code}", $user->id, $booking->id);

            $invoice = Invoice::create([
                'booking_id' => $booking->id,
                'invoice_number' => $this->generateUniqueCode('INV', fn ($code) => Invoice::withTrashed()->where('invoice_number', $code)->exists()),
                'invoice_type' => 'booking',
                'due_date' => $booking->payment_due_at->toDateString(),
                'subtotal_amount' => $totalAmount,
                'discount_amount' => 0,
                'late_fee_amount' => 0,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'status' => InvoiceStatus::Unpaid,
                'issued_at' => now(),
            ]);

            $invoice->items()->create(['label' => 'Sewa Bulan Pertama', 'item_type' => 'rent', 'quantity' => 1, 'unit_price' => $monthlyPrice, 'amount' => $monthlyPrice]);
            $invoice->items()->create(['label' => 'Deposit', 'item_type' => 'deposit', 'quantity' => 1, 'unit_price' => $depositAmount, 'amount' => $depositAmount]);

            if ($adminFee > 0) {
                $invoice->items()->create(['label' => 'Biaya Admin', 'item_type' => 'admin_fee', 'quantity' => 1, 'unit_price' => $adminFee, 'amount' => $adminFee]);
            }

            return $booking->fresh(['guests', 'documents', 'invoices.items']);
        });
    }

    public function expire(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($locked->status !== BookingStatus::AwaitingPayment || $locked->payment_due_at === null || $locked->payment_due_at->isFuture()) {
                return;
            }

            $locked->update([
                'status' => BookingStatus::Expired,
                'cancelled_at' => now(),
                'cancellation_reason' => 'Kedaluwarsa — pembayaran tidak diterima sebelum batas waktu.',
            ]);

            $room = Room::query()->lockForUpdate()->find($locked->room_id);

            if ($room && $room->status === RoomStatus::Held) {
                $room->recordStatusChange(RoomStatus::Available, "Penahanan kedaluwarsa untuk pemesanan {$locked->booking_code}", null, $locked->id);
            }

            $locked->invoices()->where('status', InvoiceStatus::Unpaid)->update(['status' => InvoiceStatus::Cancelled]);
        });
    }

    public function confirm(Booking $booking, ?User $verifiedBy = null): Lease
    {
        return DB::transaction(function () use ($booking, $verifiedBy) {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($locked->status === BookingStatus::ConvertedToLease) {
                return Lease::query()->where('booking_id', $locked->id)->firstOrFail();
            }

            $locked->update([
                'status' => BookingStatus::Confirmed,
                'confirmed_at' => now(),
                'verified_by' => $verifiedBy?->id,
                'verified_at' => now(),
            ]);

            $tenant = Tenant::query()->firstOrCreate(
                ['user_id' => $locked->user_id],
                [
                    'room_id' => $locked->room_id,
                    'booking_id' => $locked->id,
                    'status' => TenantStatus::Active,
                    'joined_at' => $locked->start_date,
                ],
            );

            if ($tenant->wasRecentlyCreated === false) {
                $tenant->update([
                    'room_id' => $locked->room_id,
                    'booking_id' => $locked->id,
                    'status' => TenantStatus::Active,
                    'joined_at' => $locked->start_date,
                    'moved_out_at' => null,
                ]);
            }

            $lease = Lease::create([
                'lease_number' => $this->generateUniqueCode('LSE', fn ($code) => Lease::withTrashed()->where('lease_number', $code)->exists()),
                'tenant_id' => $tenant->id,
                'room_id' => $locked->room_id,
                'booking_id' => $locked->id,
                'start_date' => $locked->start_date,
                'end_date' => $locked->start_date->copy()->addMonthsNoOverflow($locked->duration_months),
                'duration_months' => $locked->duration_months,
                'monthly_price' => $locked->monthly_price,
                'deposit_amount' => $locked->deposit_amount,
                'billing_cycle_day' => min((int) $locked->start_date->format('d'), 28),
                'status' => LeaseStatus::Active,
                'approved_by' => $verifiedBy?->id,
                'approved_at' => now(),
            ]);

            Deposit::create([
                'tenant_id' => $tenant->id,
                'lease_id' => $lease->id,
                'amount' => $locked->deposit_amount,
                'status' => DepositStatus::Held,
                'held_at' => now()->toDateString(),
            ]);

            $room = Room::query()->lockForUpdate()->find($locked->room_id);

            if ($room) {
                $room->recordStatusChange(RoomStatus::Occupied, "Kamar terisi melalui pemesanan {$locked->booking_code}", $verifiedBy?->id, $locked->id);
            }

            $locked->update(['status' => BookingStatus::ConvertedToLease]);

            $this->notificationDispatcher->dispatch($locked->user, 'booking_confirmed', [
                'name' => $locked->user->name,
                'room_name' => $room?->name ?? "Kamar {$room?->room_number}",
                'booking_code' => $locked->booking_code,
            ]);
            $this->notificationDispatcher->dispatch($locked->user, 'booking_confirmed_wa', [
                'name' => $locked->user->name,
                'room_name' => $room?->name ?? "Kamar {$room?->room_number}",
                'booking_code' => $locked->booking_code,
            ]);

            return $lease;
        });
    }

    private function generateUniqueCode(string $prefix, callable $exists): string
    {
        do {
            $code = sprintf('%s-%s-%s', $prefix, now()->format('ymd'), Str::upper(Str::random(5)));
        } while ($exists($code));

        return $code;
    }
}
