<?php

namespace App\Enums;

enum TransactionType: string
{
    case Sale = 'sale';
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Penjualan',
            self::Return => 'Retur',
        };
    }
}
