<?php

namespace App\Concerns;

/**
 * Trait for models that store monetary values as integers (cents).
 *
 * Provides helper methods to format integer-based money values
 * to human-readable IDR currency strings.
 */
trait HasMoneyAttributes
{
    /**
     * Format an integer cents value to IDR currency string.
     */
    public function formatMoney(int $cents): string
    {
        return 'Rp '.number_format($cents / 100, 0, ',', '.');
    }

    /**
     * Convert rupiah amount to cents for storage.
     */
    public static function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert cents to rupiah amount for display.
     */
    public static function toRupiah(int $cents): float
    {
        return $cents / 100;
    }
}
