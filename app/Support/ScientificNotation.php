<?php

namespace App\Support;

class ScientificNotation
{
    /**
     * Turn a count into report form, for example 490000 into 4,9 × 10⁵.
     * Counts below 10, and values that are not plain numbers, are left unchanged.
     */
    public static function format(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;
        if ($number == 0.0 || abs($number) < 10) {
            return null;
        }

        $sign = $number < 0 ? '-' : '';
        $absolute = abs($number);
        $exponent = (int) floor(log10($absolute));
        $coefficient = $absolute / (10 ** $exponent);
        $rounded = round($coefficient, 1);

        if ($rounded >= 10.0) {
            $rounded = 1.0;
            $exponent++;
        }

        $mantissa = number_format($rounded, 1, ',', '');

        return $sign.$mantissa.' × 10'.self::superscript($exponent);
    }

    public static function superscript(int $exponent): string
    {
        $digits = [
            '0' => '⁰',
            '1' => '¹',
            '2' => '²',
            '3' => '³',
            '4' => '⁴',
            '5' => '⁵',
            '6' => '⁶',
            '7' => '⁷',
            '8' => '⁸',
            '9' => '⁹',
            '-' => '⁻',
        ];

        $superscript = '';
        foreach (str_split((string) $exponent) as $character) {
            $superscript .= $digits[$character] ?? $character;
        }

        return $superscript;
    }
}
