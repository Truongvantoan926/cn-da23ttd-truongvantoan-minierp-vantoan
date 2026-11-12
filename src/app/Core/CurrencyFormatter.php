<?php

declare(strict_types=1);

namespace App\Core;

final class CurrencyFormatter
{
    public static function format(int|float|string $amount): string
    {
        return number_format((float) $amount, 0, ',', '.');
    }

    public static function formatInput(string $amount): string
    {
        if (!is_numeric($amount) || !str_contains($amount, '.')) {
            return $amount;
        }

        [$integer, $fraction] = explode('.', $amount, 2);
        $fraction = rtrim($fraction, '0');

        return $fraction === '' ? $integer : $integer . '.' . $fraction;
    }
}
