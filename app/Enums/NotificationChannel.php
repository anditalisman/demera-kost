<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case InApp = 'in_app';
    case Email = 'email';
    case Whatsapp = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::InApp => 'Dalam Aplikasi',
            self::Email => 'Email',
            self::Whatsapp => 'WhatsApp',
        };
    }
}
