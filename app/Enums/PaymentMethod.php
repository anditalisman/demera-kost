<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case VirtualAccount = 'virtual_account';
    case Qris = 'qris';
    case Ewallet = 'ewallet';
    case ManualTransfer = 'manual_transfer';
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::VirtualAccount => 'Virtual Account',
            self::Qris => 'QRIS',
            self::Ewallet => 'E-Wallet',
            self::ManualTransfer => 'Transfer Bank Manual',
            self::Cash => 'Tunai',
        };
    }
}
