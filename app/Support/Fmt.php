<?php

namespace App\Support;

class Fmt
{
    /** Tampilan angka gaya Indonesia: 1.234,5 (nol di belakang koma dibuang). */
    public static function num($value, int $decimals = 3): string
    {
        $s = number_format((float) $value, $decimals, ',', '.');

        return str_contains($s, ',') ? rtrim(rtrim($s, '0'), ',') : $s;
    }

    /** Nilai untuk diisikan kembali ke input: 12,5 (koma sebagai desimal). */
    public static function input($value): string
    {
        $s = (string) $value;
        if (str_contains($s, '.')) {
            $s = rtrim(rtrim($s, '0'), '.');
        }

        return str_replace('.', ',', $s);
    }
}
