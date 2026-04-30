<?php

namespace App\Helpers;

class FormatHelper
{
    public static function rupiah($value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    public static function durationHuman(int $minutes): string
    {
        if ($minutes < 1) {
            return '0 mnt';
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        if ($h > 0 && $m > 0) {
            return $h.' jam '.$m.' mnt';
        }
        if ($h > 0) {
            return $h.' jam';
        }

        return $m.' mnt';
    }
}
