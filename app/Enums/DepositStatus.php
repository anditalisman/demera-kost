<?php

namespace App\Enums;

enum DepositStatus: string
{
    case Held = 'held';
    case PartiallyReturned = 'partially_returned';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Held => 'Ditahan',
            self::PartiallyReturned => 'Sebagian Dikembalikan',
            self::Returned => 'Dikembalikan Penuh',
        };
    }
}
