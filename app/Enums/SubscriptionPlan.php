<?php

namespace App\Enums;

enum SubscriptionPlan: string
{
    case Basic = 'basic';
    case Pro = 'pro';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::Basic => 'Basic',
            self::Pro => 'Pro',
            self::Enterprise => 'Enterprise',
        };
    }

    public function maxProducts(): int
    {
        return match ($this) {
            self::Basic => 100,
            self::Pro => 500,
            self::Enterprise => PHP_INT_MAX,
        };
    }

    public function maxUsers(): int
    {
        return match ($this) {
            self::Basic => 2,
            self::Pro => 10,
            self::Enterprise => PHP_INT_MAX,
        };
    }

    public function maxTransactionsPerMonth(): int
    {
        return match ($this) {
            self::Basic => 500,
            self::Pro => 5000,
            self::Enterprise => PHP_INT_MAX,
        };
    }

    public function hasFeature(string $feature): bool
    {
        return match ($feature) {
            'reports_full' => in_array($this, [self::Pro, self::Enterprise]),
            'reports_export' => $this === self::Enterprise,
            'white_label' => $this === self::Enterprise,
            'supplier_management' => in_array($this, [self::Pro, self::Enterprise]),
            'multi_unit_conversion' => in_array($this, [self::Pro, self::Enterprise]),
            default => true,
        };
    }

    /**
     * @return int Price in cents (IDR)
     */
    public function monthlyPrice(): int
    {
        return match ($this) {
            self::Basic => 19900000,
            self::Pro => 49900000,
            self::Enterprise => 99900000,
        };
    }
}
