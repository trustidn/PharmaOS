<?php

if (! function_exists('parse_money_to_float')) {
    /**
     * Parse user input (Rupiah) to float. Accepts comma or dot as decimal separator.
     * Examples: "2500,2" -> 2500.2, "2.500,5" -> 2500.5, "2500" -> 2500.0
     */
    function parse_money_to_float(?string $input): float
    {
        if ($input === null || trim($input) === '') {
            return 0.0;
        }

        $s = trim(str_replace(' ', '', $input));

        if (str_contains($s, ',')) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        }

        return (float) $s;
    }
}

if (! function_exists('format_rupiah_input')) {
    /**
     * Format cents to string for input (Rupiah with comma as decimal separator).
     */
    function format_rupiah_input(int $cents, int $decimals = 2): string
    {
        $value = $cents / 100;

        return number_format($value, $decimals, ',', '');
    }
}
