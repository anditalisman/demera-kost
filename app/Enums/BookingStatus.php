<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case AwaitingPayment = 'awaiting_payment';
    case Confirmed = 'confirmed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case ConvertedToLease = 'converted_to_lease';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Diproses',
            self::AwaitingPayment => 'Menunggu Pembayaran',
            self::Confirmed => 'Terkonfirmasi',
            self::Expired => 'Kedaluwarsa',
            self::Cancelled => 'Dibatalkan',
            self::ConvertedToLease => 'Menjadi Kontrak Sewa',
        };
    }
}
