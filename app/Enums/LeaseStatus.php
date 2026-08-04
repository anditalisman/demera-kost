<?php

namespace App\Enums;

enum LeaseStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Active = 'active';
    case EndingSoon = 'ending_soon';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Extended = 'extended';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingApproval => 'Menunggu Persetujuan',
            self::Active => 'Aktif',
            self::EndingSoon => 'Akan Berakhir',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Extended => 'Diperpanjang',
        };
    }
}
