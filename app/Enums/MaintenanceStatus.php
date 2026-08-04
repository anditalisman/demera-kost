<?php

namespace App\Enums;

enum MaintenanceStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Waiting = 'waiting';
    case Completed = 'completed';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Baru',
            self::InProgress => 'Diproses',
            self::Waiting => 'Menunggu',
            self::Completed => 'Selesai',
            self::Closed => 'Ditutup',
        };
    }
}
