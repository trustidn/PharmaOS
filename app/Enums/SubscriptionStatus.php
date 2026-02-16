<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Trial => 'Trial',
            self::Active => 'Aktif',
            self::PastDue => 'Jatuh Tempo',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function isUsable(): bool
    {
        return in_array($this, [self::Trial, self::Active]);
    }
}
