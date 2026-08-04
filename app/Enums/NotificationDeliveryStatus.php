<?php

namespace App\Enums;

enum NotificationDeliveryStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
    case Retrying = 'retrying';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Sent => 'Terkirim',
            self::Failed => 'Gagal',
            self::Retrying => 'Mencoba Ulang',
        };
    }
}
