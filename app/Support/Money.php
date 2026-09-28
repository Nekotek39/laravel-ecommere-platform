<?php

namespace App\Support;

/**
 * Monetary amounts in the application are stored as integers in cents.
 */
final class Money
{
    /**
     * Converts an amount entered by a user (e.g. "199.99" or "199,99") to cents.
     */
    public static function toCents(string|int|float|null $amount): ?int
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], (string) $amount);

        return (int) round(((float) $normalized) * 100);
    }

    /**
     * Converts cents to a decimal value, e.g. to pre-fill a form field.
     */
    public static function toDecimal(?int $cents): ?string
    {
        if ($cents === null) {
            return null;
        }

        return number_format($cents / 100, 2, '.', '');
    }

    /**
     * Formats an amount for display, e.g. "1,299.99 PLN".
     */
    public static function format(?int $cents): string
    {
        return number_format(($cents ?? 0) / 100, 2, '.', ',').' '.config('shop.currency_symbol');
    }
}
