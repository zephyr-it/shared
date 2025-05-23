<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Helpers;

use ReflectionEnum;

class EnumHelpers
{
    /**
     * Retrieve all the labels of an enum class using its `getLabel` method.
     *
     * Note: This assumes each enum case implements a `getLabel` method.
     */
    public static function getEnumLabels(string $enumClass): array
    {
        $reflection = new ReflectionEnum($enumClass);

        return array_map(
            fn ($case) => $case->getValue()->getLabel(),
            $reflection->getCases()
        );
    }

    /**
     * Retrieve all the backing values of an enum class.
     */
    public static function getEnumValues(string $enumClass): array
    {
        $reflection = new ReflectionEnum($enumClass);

        return array_map(
            fn ($case) => $case->getBackingValue(),
            $reflection->getCases()
        );
    }

    /**
     * Filter enum statuses, excluding specified cases.
     */
    public static function getFilteredEnumStatuses(string $enumClass, array $excludedCases = []): array
    {
        return \Illuminate\Support\Collection::make($enumClass::cases())
            ->filter(fn ($status) => ! in_array($status->value, $excludedCases))
            ->mapWithKeys(fn ($status) => [$status->value => $status->getLabel()])
            ->toArray();
    }
}
