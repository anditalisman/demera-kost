<?php

namespace App\Enums;

enum TenantStatus: string
{
    case Prospective = 'prospective';
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Prospective => 'Calon Penyewa',
            self::Active => 'Penyewa Aktif',
            self::Inactive => 'Tidak Aktif',
        };
    }
}
