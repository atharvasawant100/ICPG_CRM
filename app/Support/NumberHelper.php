<?php

namespace App\Support;

class NumberHelper
{
    /**
     * Format a number in compact human-readable notation (e.g., 1000 -> 1k, 625000 -> 625k, 7500000 -> 7.5M).
     */
    public static function formatCompact($amount, int $precision = 1): string
    {
        if ($amount === null || !is_numeric($amount)) {
            return '0';
        }

        $amount = (float) $amount;
        $abs = abs($amount);
        $sign = $amount < 0 ? '-' : '';

        if ($abs >= 1_000_000_000) {
            $val = round($abs / 1_000_000_000, $precision);
            return $sign . ($val == (int) $val ? (int) $val : $val) . 'B';
        }

        if ($abs >= 1_000_000) {
            $val = round($abs / 1_000_000, $precision);
            return $sign . ($val == (int) $val ? (int) $val : $val) . 'M';
        }

        if ($abs >= 1_000) {
            $val = round($abs / 1_000, $precision);
            return $sign . ($val == (int) $val ? (int) $val : $val) . 'k';
        }

        return $sign . (fmod($abs, 1) !== 0.0 ? number_format($abs, 2) : number_format($abs, 0));
    }

    /**
     * Format a number using Indian compact notation (Cr, L, k).
     * e.g., 10000000 -> 1Cr, 190000 -> 1.9L, 100000 -> 1L, 90000 -> 90k, 24999.99 -> 25k, 1000 -> 1k.
     */
    public static function formatIndianCompact($amount, int $precision = 2): string
    {
        if ($amount === null || !is_numeric($amount)) {
            return '0';
        }

        $amount = (float) $amount;
        $abs = abs($amount);
        $sign = $amount < 0 ? '-' : '';

        if ($abs >= 10_000_000) {
            $val = rtrim(rtrim(number_format($abs / 10_000_000, $precision, '.', ''), '0'), '.');
            return $sign . $val . 'Cr';
        }

        if ($abs >= 100_000) {
            $val = rtrim(rtrim(number_format($abs / 100_000, $precision, '.', ''), '0'), '.');
            return $sign . $val . 'L';
        }

        if ($abs >= 1_000) {
            $val = rtrim(rtrim(number_format($abs / 1_000, $precision, '.', ''), '0'), '.');
            return $sign . $val . 'k';
        }

        return $sign . (fmod($abs, 1) !== 0.0 ? number_format($abs, 2) : number_format($abs, 0));
    }

    /**
     * Format an amount with the Rupee symbol in compact notation.
     */
    public static function rupee($amount, int $precision = 1): string
    {
        return '₹' . self::formatCompact($amount, $precision);
    }

    /**
     * Format an amount with the Rupee symbol in Indian compact notation (₹1.9L, ₹90k, ₹1k).
     */
    public static function rupeeIndian($amount, int $precision = 2): string
    {
        return '₹' . self::formatIndianCompact($amount, $precision);
    }
}
