<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Helpers;

class ArrayHelpers
{
    /**
     * Flatten a multidimensional array with a dot notation.
     */
    public static function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result += self::flattenArray($value, $newKey);
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}
