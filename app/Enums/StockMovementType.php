<?php

namespace App\Enums;

enum StockMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Adjustment = 'adjustment';
    case Return = 'return';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Masuk',
            self::Out => 'Keluar',
            self::Adjustment => 'Penyesuaian',
            self::Return => 'Retur',
            self::Expired => 'Kadaluarsa',
        };
    }
}
