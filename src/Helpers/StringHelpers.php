<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Helpers;

class StringHelpers
{
    /**
     * Sanitize and format the input string by removing extra spaces, trimming, and applying transformations.
     */
    public static function sanitizeAndFormat(?string $input, bool $toLowerCase = false, bool $removeSpaces = false, bool $formatWords = true): ?string
    {
        if (is_null($input) || trim($input) === '') {
            return null;
        }

        // Step 1: Trim and normalize whitespace
        $sanitized = trim($input);

        if (! $removeSpaces) {
            $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        }

        // Step 2: Remove all spaces if specified
        if ($removeSpaces) {
            $sanitized = str_replace(' ', '', $sanitized);
        }

        // Step 3: Apply word-specific formatting rules only if spaces are not removed
        if ($formatWords && ! $removeSpaces) {
            $sanitized = implode(' ', array_map(function ($word) {
                return strlen($word) < 3 ? strtoupper($word) : ucfirst(strtolower($word));
            }, explode(' ', $sanitized)));
        }

        // Step 4: Convert to lowercase if specified
        return $toLowerCase ? strtolower($sanitized) : $sanitized;
    }

    /**
     * Remove special characters and unnecessary characters from a string.
     * Spaces are preserved unless explicitly removed by excluding them from `$allowedCharacters`.
     */
    public static function sanitizeSpecialCharacters(?string $input, string $allowedCharacters = ' '): ?string
    {
        if (is_null($input) || trim($input) === '') {
            return null;
        }

        // Replace disallowed characters with a space
        $pattern = '/[^a-zA-Z0-9' . preg_quote($allowedCharacters, '/') . ']/';

        // Replace special characters with space
        $sanitized = preg_replace($pattern, ' ', $input);

        // Collapse multiple spaces into one
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);

        return trim($sanitized);
    }

    /**
     * Normalize a string by removing spaces and converting to lowercase.
     */
    public static function normalizeString(?string $value): string
    {
        return strtolower(
            preg_replace('/\s+/', '', self::sanitizeSpecialCharacters($value ?? ''))
        );
    }
}
