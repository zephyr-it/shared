<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Helpers;

use NumberFormatter;

class FormatHelpers
{
    /**
     * Generates an "Add" button label, optionally prefixed with a label.
     */
    public static function formatAddButtonLabel(?string $label = null): string
    {
        return $label ? __('shared::labels.add') . " {$label}" : __('shared::labels.add');
    }

    /**
     * Generates a copy message, including an optional label prefix.
     */
    public static function formatCopyMessage(?string $label): string
    {
        return $label ? "{$label} " . __('shared::messages.copied') : __('shared::messages.copied');
    }

    /**
     * Short number format: 1K, 1.5M, etc.
     */
    public static function formatNumberShort(float | int | string $number, int $precision = 1): string
    {
        $number = (float) $number;

        if ($number < 1000) {
            return (string) $number;
        }

        $units = ['K', 'M', 'B', 'T'];
        $unitIndex = (int) floor(log10($number) / 3) - 1;

        return round($number / (1000 ** ($unitIndex + 1)), $precision) . $units[$unitIndex];
    }

    /**
     * Convert a number to Indian currency format.
     */
    public static function numberToIndianFormat($number): string
    {
        if (! is_numeric($number)) {
            return '0';
        }

        $number = round((float) $number, 2);

        $number = (string) $number;

        $isNegative = $number[0] === '-';

        if ($isNegative) {
            $number = substr($number, 1);
        }

        $parts = explode('.', $number);
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? '.' . $parts[1] : '';

        $lastThreeDigits = substr($integerPart, -3);
        $remainingDigits = substr($integerPart, 0, -3);

        if (strlen($remainingDigits) > 0) {
            $remainingDigits = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remainingDigits);
            $formattedNumber = $remainingDigits . ',' . $lastThreeDigits;
        } else {
            $formattedNumber = $lastThreeDigits;
        }

        return ($isNegative ? '-' : '') . $formattedNumber . $decimalPart;
    }

    /**
     * Converts a number to its word, currency, ordinal or short form.
     */
    public static function numberToWord(int | float | string $number, string $type = 'spellout', ?string $locale = null, ?string $currency = null): string
    {
        $locale ??= app()->getLocale() ?? 'en';

        if (! class_exists(NumberFormatter::class)) {
            return (string) $number;
        }

        $formatterType = match ($type) {
            'ordinal' => NumberFormatter::ORDINAL,
            'currency' => NumberFormatter::CURRENCY,
            'spellout' => NumberFormatter::SPELLOUT,
            'short' => NumberFormatter::DECIMAL,
            default => NumberFormatter::SPELLOUT,
        };

        $formatter = new NumberFormatter($locale, $formatterType);

        return match ($type) {
            'currency' => $currency
                ? $formatter->formatCurrency((float) $number, $currency)
                : $formatter->formatCurrency((float) $number, 'USD'),

            'short' => self::formatNumberShort($number),

            default => ucfirst($formatter->format($number) ?? (string) $number),
        };
    }
}
