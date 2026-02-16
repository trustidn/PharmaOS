<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Voided = 'voided';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Tertunda',
            self::Completed => 'Selesai',
            self::Voided => 'Dibatalkan',
            self::Refunded => 'Dikembalikan',
        };
    }
}
