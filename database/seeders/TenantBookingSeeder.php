<?php

namespace Database\Seeders;

use App\Domain\Living\Models\Booking;
use App\Domain\Living\Models\BookingGuest;
use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\InvoiceItem;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\Payment;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\Tenant;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantBookingSeeder extends Seeder
{
    public function run(): void
    {
        if (Tenant::query()->exists()) {
            return;
        }

        $occupiedRooms = Room::query()->where('status', RoomStatus::Occupied)->get();

        foreach ($occupiedRooms as $index => $room) {
            $user = User::query()->updateOrCreate(
                ['email' => "penyewa".($index + 1)."@demera.my.id"],
                [
                    'name' => $index === 0 ? 'Budi Santoso' : 'Siti Rahma',
                    'whatsapp_number' => '+62813'.str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'whatsapp_verified_at' => now(),
                    'terms_accepted_at' => now(),
                    'is_active' => true,
                ],
            );
            $user->syncRoles(['customer']);

            $booking = Booking::create([
                'booking_code' => 'BK-'.now()->subMonths(2)->format('Ymd').'-'.Str::upper(Str::random(6)),
                'user_id' => $user->id,
                'room_id' => $room->id,
                'status' => BookingStatus::ConvertedToLease,
                'start_date' => now()->subMonths(2)->startOfMonth()->toDateString(),
                'duration_months' => 6,
                'monthly_price' => $room->monthly_price,
                'deposit_amount' => $room->deposit_amount,
                'admin_fee' => 25000,
                'discount_amount' => 0,
                'total_amount' => bcadd((string) $room->monthly_price, (string) $room->deposit_amount, 2),
                'confirmed_at' => now()->subMonths(2),
                'verified_at' => now()->subMonths(2),
            ]);

            BookingGuest::create([
                'booking_id' => $booking->id,
                'full_name' => $user->name,
                'identity_number' => '31710'.str_pad((string) ($index + 1), 11, '0', STR_PAD_LEFT),
                'phone' => $user->whatsapp_number,
                'email' => $user->email,
                'relationship' => 'self',
                'is_primary' => true,
            ]);

            $tenant = Tenant::create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'booking_id' => $booking->id,
                'status' => 'active',
                'emergency_contact_name' => 'Keluarga '.$user->name,
                'emergency_contact_phone' => '+62815'.str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT),
                'emergency_contact_relationship' => 'Orang Tua',
                'joined_at' => now()->subMonths(2)->startOfMonth(),
            ]);

            $lease = Lease::create([
                'lease_number' => 'LC-'.now()->subMonths(2)->format('Ymd').'-'.Str::upper(Str::random(6)),
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'booking_id' => $booking->id,
                'start_date' => now()->subMonths(2)->startOfMonth()->toDateString(),
                'end_date' => now()->addMonths(4)->endOfMonth()->toDateString(),
                'duration_months' => 6,
                'monthly_price' => $room->monthly_price,
                'deposit_amount' => $room->deposit_amount,
                'billing_cycle_day' => 1,
                'status' => LeaseStatus::Active,
                'terms' => 'Sewa dibayar di muka setiap tanggal 1. Deposit dikembalikan sesuai kebijakan pembatalan.',
                'signed_at' => now()->subMonths(2),
                'approved_at' => now()->subMonths(2),
            ]);

            // Paid invoice for last month.
            $paidInvoice = Invoice::create([
                'invoice_number' => 'INV-'.now()->subMonth()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'lease_id' => $lease->id,
                'tenant_id' => $tenant->id,
                'invoice_type' => 'rent',
                'period_start' => now()->subMonth()->startOfMonth()->toDateString(),
                'period_end' => now()->subMonth()->endOfMonth()->toDateString(),
                'due_date' => now()->subMonth()->addDays(4)->toDateString(),
                'subtotal_amount' => $room->monthly_price,
                'total_amount' => $room->monthly_price,
                'paid_amount' => $room->monthly_price,
                'status' => InvoiceStatus::Paid,
                'issued_at' => now()->subMonth(),
            ]);

            InvoiceItem::create([
                'invoice_id' => $paidInvoice->id,
                'label' => 'Sewa Bulanan',
                'item_type' => 'rent',
                'quantity' => 1,
                'unit_price' => $room->monthly_price,
                'amount' => $room->monthly_price,
            ]);

            Payment::create([
                'payment_code' => 'PAY-'.now()->subMonth()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'invoice_id' => $paidInvoice->id,
                'method' => PaymentMethod::ManualTransfer,
                'amount' => $room->monthly_price,
                'status' => PaymentStatus::Paid,
                'gateway_provider' => 'manual',
                'paid_at' => now()->subMonth()->addDays(2),
                'verified_at' => now()->subMonth()->addDays(2),
            ]);

            // Current month invoice, still unpaid.
            Invoice::create([
                'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'lease_id' => $lease->id,
                'tenant_id' => $tenant->id,
                'invoice_type' => 'rent',
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'due_date' => now()->startOfMonth()->addDays(4)->toDateString(),
                'subtotal_amount' => $room->monthly_price,
                'total_amount' => $room->monthly_price,
                'paid_amount' => 0,
                'status' => InvoiceStatus::Unpaid,
                'issued_at' => now()->startOfMonth(),
            ]);
        }

        $this->seedPendingBooking();
        $this->seedDemoCustomer();
    }

    /**
     * A customer with a booking still awaiting payment, illustrating the
     * "awaiting_payment" room status without a lease/tenant yet existing.
     */
    private function seedPendingBooking(): void
    {
        $room = Room::query()->where('status', RoomStatus::AwaitingPayment)->first();

        if (! $room) {
            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => 'calon.penyewa@demera.my.id'],
            [
                'name' => 'Andi Wijaya',
                'whatsapp_number' => '+628140000001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'whatsapp_verified_at' => now(),
                'terms_accepted_at' => now(),
                'is_active' => true,
            ],
        );
        $user->syncRoles(['customer']);

        Booking::create([
            'booking_code' => 'BK-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'user_id' => $user->id,
            'room_id' => $room->id,
            'status' => BookingStatus::AwaitingPayment,
            'start_date' => now()->addWeek()->toDateString(),
            'duration_months' => 3,
            'monthly_price' => $room->monthly_price,
            'deposit_amount' => $room->deposit_amount,
            'admin_fee' => 25000,
            'discount_amount' => 0,
            'total_amount' => bcadd((string) $room->monthly_price, (string) $room->deposit_amount, 2),
            'payment_due_at' => now()->addDay(),
        ]);
    }

    private function seedDemoCustomer(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'customer@demera.my.id'],
            [
                'name' => 'Calon Penyewa Demera',
                'whatsapp_number' => '+628150000001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'whatsapp_verified_at' => now(),
                'terms_accepted_at' => now(),
                'is_active' => true,
            ],
        );
        $user->syncRoles(['customer']);
    }
}
