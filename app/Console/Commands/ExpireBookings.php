<?php

namespace App\Console\Commands;

use App\Domain\Living\Models\Booking;
use App\Domain\Living\Services\BookingLifecycleService;
use App\Enums\BookingStatus;
use Illuminate\Console\Command;

class ExpireBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Expire awaiting-payment bookings past their payment due time and release the held room.';

    public function handle(BookingLifecycleService $bookingLifecycleService): int
    {
        $candidates = Booking::query()
            ->where('status', BookingStatus::AwaitingPayment)
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', now())
            ->get();

        foreach ($candidates as $booking) {
            $bookingLifecycleService->expire($booking);
        }

        $this->info("{$candidates->count()} booking kedaluwarsa diproses.");

        return self::SUCCESS;
    }
}
