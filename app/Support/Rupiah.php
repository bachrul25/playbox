<?php

namespace App\Support;

class Rupiah
{
    /**
     * Format angka menjadi format Rupiah Indonesia.
     * Contoh: 1000000 -> "Rp1.000.000".
     */
    public static function format(float|int|string|null $value, bool $withDecimal = false): string
    {
        $value = (float) ($value ?? 0);
        $decimals = $withDecimal ? 2 : 0;

        return 'Rp'.number_format($value, $decimals, ',', '.');
    }
}
