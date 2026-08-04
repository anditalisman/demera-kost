<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Available = 'available';
    case Held = 'held';
    case AwaitingPayment = 'awaiting_payment';
    case Occupied = 'occupied';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Held => 'Ditahan Sementara',
            self::AwaitingPayment => 'Menunggu Pembayaran',
            self::Occupied => 'Terisi',
            self::Maintenance => 'Dalam Perawatan',
            self::Inactive => 'Tidak Aktif',
        };
    }

    /**
     * Statuses considered publicly bookable / visible as "open" on the catalog.
     */
    public static function publiclyAvailable(): array
    {
        return [self::Available];
    }
}
