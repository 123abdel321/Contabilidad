<?php

namespace App\Pdf\Core;

class Formatter
{
    public static function number($value, int $decimals = 0): string
    {
        return number_format($value, $decimals);
    }

    public static function currency($value): string
    {
        return '$ ' . number_format($value, 0);
    }

    public static function date($value, string $format = 'd/m/Y'): string
    {
        if (!$value) return '';
        return \Carbon\Carbon::parse($value)->format($format);
    }

    public static function datetime($value): string
    {
        if (!$value) return '';
        return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
    }
}