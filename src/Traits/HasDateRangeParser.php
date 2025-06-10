<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Traits;

use Carbon\Carbon;
use InvalidArgumentException;

trait HasDateRangeParser
{
    /**
     * Parse and normalize date range into Carbon instances (startOfDay → endOfDay).
     */
    protected function parseDateRange(string | array $dateFilter): array
    {
        if (is_array($dateFilter)) {
            [$startDate, $endDate] = $dateFilter;
        } elseif (is_string($dateFilter) && str_contains($dateFilter, ' - ')) {
            [$startDate, $endDate] = explode(' - ', $dateFilter);
        } else {
            throw new InvalidArgumentException('The provided date range is invalid.');
        }

        return [
            $this->toCarbon($startDate)->startOfDay(),
            $this->toCarbon($endDate)->endOfDay(),
        ];
    }

    /**
     * Parse date range and return formatted strings.
     */
    protected function parseDateRangeAsStrings(string | array $dateFilter, string $format = 'Y-m-d'): array
    {
        [$start, $end] = $this->parseDateRange($dateFilter);

        return [
            $start->format($format),
            $end->format($format),
        ];
    }

    /**
     * Convert string to Carbon (supports Y-m-d and d/m/Y).
     */
    protected function toCarbon(string | Carbon $date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        $date = trim($date);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return Carbon::createFromFormat('Y-m-d', $date);
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            return Carbon::createFromFormat('d/m/Y', $date);
        }

        throw new InvalidArgumentException("Unsupported date format: {$date}");
    }

    public function testParseDateRange(string | array $value): array
    {
        return $this->parseDateRange($value);
    }

    public function testParseDateRangeAsStrings(string | array $value, string $format = 'Y-m-d'): array
    {
        return $this->parseDateRangeAsStrings($value, $format);
    }
}
