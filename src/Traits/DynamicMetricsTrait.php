<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Traits;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Collection;

trait DynamicMetricsTrait
{
    /**
     * Fetches dynamic metrics with support for conditions, flexible filters, and grouping.
     */
    public function fetchDynamicMetricsData(
        array | string $models,
        array $metrics,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?Closure $additionalFilters = null,
        string | Closure | null $groupByColumn = null,
        string $dateColumn = 'created_at',
        array $relations = []
    ): Collection {
        [$startDate, $endDate] = $this->prepareDateRange($startDate, $endDate);
        $interval = $this->determineDateInterval($startDate, $endDate);
        $dateRanges = $this->generateDateRange($startDate, $endDate, $interval);

        $data = collect();

        foreach ((array) $models as $model) {
            $aggregatedData = collect();

            foreach ($dateRanges as ['startDay' => $startDay, 'endDay' => $endDay]) {
                $query = $model::query();

                $query->whereBetween($dateColumn, [$startDay, $endDay]);

                if ($additionalFilters) {
                    $query = $additionalFilters($query);
                }

                $intervalData = $query->get();

                if ($intervalData->isEmpty()) {
                    continue;
                }

                $groupedIntervalData = $this->recursiveGroupBy(
                    $intervalData,
                    $this->getGroupByClosure($groupByColumn),
                    $metrics
                );

                $aggregatedData = collect(
                    $this->deepMerge($aggregatedData->toArray(), $groupedIntervalData->toArray())
                );
            }

            $data = collect($this->deepMerge($data->toArray(), $aggregatedData->toArray()));
        }

        return $data;
    }

    /**
     * Generates chart data for a metric across multiple models.
     */
    public function getMetricChartData(
        array | string $models,
        string $metricType,
        string $column = 'id',
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?Closure $condition = null,
        string $dateColumn = 'created_at'
    ): array {
        [$startDate, $endDate] = $this->prepareDateRange($startDate, $endDate);
        $interval = $this->determineDateInterval($startDate, $endDate);
        $dates = $this->generateDateRange($startDate, $endDate, $interval);

        return array_map(
            fn ($date) => $this->calculateIntervalMetric((array) $models, $metricType, $column, $date['startDay']->toDateString(), $interval, $condition, $dateColumn),
            $dates
        );
    }

    /**
     * Gets the sum or count of a specified metric across multiple models.
     */
    public function getMetricData(
        array | string $models,
        string $metricType,
        string $column,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?Closure $condition = null,
        string $dateColumn = 'created_at'
    ): int | float | string {
        [$startDate, $endDate] = $this->prepareDateRange($startDate, $endDate);

        return numberToIndianFormat(array_reduce((array) $models, function ($result, $model) use ($metricType, $column, $startDate, $endDate, $condition, $dateColumn) {
            $query = $model::query();

            $query->whereBetween($dateColumn, [$startDate, $endDate]);

            if ($condition) {
                $query = $condition($query);
            }

            return $result + ($metricType === 'sum' ? $query->sum($column) : $query->count());
        }, 0));
    }

    /**
     * Retrieves the date range from filters, defaulting to the current month.
     */
    protected function getDateRange(): array
    {
        $dateRange = $this->filters['dateRange']
            ?? now()->startOfMonth()->format('d/m/Y') . ' - ' . now()->endOfMonth()->format('d/m/Y');

        [$startDate, $endDate] = explode(' - ', $dateRange);

        return [
            Carbon::createFromFormat('d/m/Y', $startDate)->startOfDay(),
            Carbon::createFromFormat('d/m/Y', $endDate)->endOfDay(),
        ];
    }

    /**
     * Applies a condition to a data group if provided.
     */
    protected function applyCondition(Collection $group, array | Closure | null $condition): Collection
    {
        if (is_callable($condition)) {
            return $condition($group);
        } elseif (is_array($condition) && isset($condition['field'], $condition['value'])) {
            return $group->where($condition['field'], $condition['operator'] ?? '=', $condition['value']);
        }

        return $group;
    }

    /**
     * Calculates the average interval in days between two date fields.
     */
    protected function calculateAverageDateInterval(Collection $group, string $column): float
    {
        $dateDiffs = $group->pluck($column)->map(fn ($date) => $this->calculateDateDifference($date));

        return $dateDiffs->isNotEmpty() ? $dateDiffs->average() : 0;
    }

    /**
     * Determines the next date based on the specified interval.
     */
    protected function calculateNextDate(string $date, string $interval): Carbon
    {
        $baseDate = Carbon::parse($date);

        return match ($interval) {
            'daily' => $baseDate->copy()->addDay(),
            'weekly' => $baseDate->copy()->addWeek(),
            'monthly' => $baseDate->copy()->addMonth(),
            'quarterly' => $baseDate->copy()->addMonths(3),
            default => $baseDate->copy()->addDay(),
        };
    }

    /**
     * Calculates a metric value for a model within a specified interval.
     */
    protected function calculateIntervalMetric(
        array $models,
        string $metricType,
        string $column,
        string $date,
        string $interval,
        ?Closure $condition,
        string $dateColumn
    ): int | float {
        $nextDate = $this->calculateNextDate($date, $interval);

        return array_reduce($models, function ($sum, $model) use ($metricType, $column, $date, $nextDate, $condition, $dateColumn) {
            $query = $model::whereBetween($dateColumn, [$date, $nextDate->subSecond()]);

            if ($condition) {
                $query = $condition($query);
            }

            return $sum + ($metricType === 'sum' ? $query->sum($column) : $query->count());
        }, 0);
    }

    /**
     * Calculates the difference in days between two dates.
     */
    protected function calculateDateDifference(array $date): int
    {
        $startDate = Carbon::parse($date['registered_date']);
        $endDate = Carbon::parse($date['settlement_date']);

        return $startDate && $endDate ? (int) $startDate->diffInDays($endDate) : 0;
    }

    /**
     * Determines the appropriate date interval (daily, weekly, etc.) for the date range.
     */
    protected function determineDateInterval(Carbon $startDate, Carbon $endDate): string
    {
        $days = $startDate->diffInDays($endDate);

        return match (true) {
            $days <= 7 => 'daily',
            $days <= 90 => 'weekly',
            $days <= 365 => 'monthly',
            default => 'quarterly',
        };
    }

    /**
     * Generates a range of dates between two dates at a specified interval.
     */
    protected function generateDateRange(Carbon $startDate, Carbon $endDate, string $interval): array
    {
        $ranges = [];
        $currentStart = $startDate->copy();

        while ($currentStart->lt($endDate)) {
            $currentEnd = $this->calculateNextDate($currentStart->format('Y-m-d'), $interval)->subSecond();

            if ($currentEnd->gt($endDate)) {
                $currentEnd = $endDate->copy();
            }

            $ranges[] = [
                'startDay' => $currentStart->copy()->startOfDay(),
                'endDay' => $currentEnd->copy()->endOfDay(),
            ];

            $currentStart = $currentEnd->copy()->addSecond();
        }

        return $ranges;
    }

    /**
     * Helper to get a grouping function for metrics data.
     */
    protected function getGroupByClosure(string | Closure | null $groupByColumn): Closure
    {
        return $groupByColumn instanceof Closure ? $groupByColumn : fn ($item) => data_get($item, $groupByColumn);
    }

    /**
     * Processes and aggregates data for a group based on specified metrics.
     */
    protected function processMetricGroup(Collection $group, array $metrics): array
    {
        return array_reduce(array_keys($metrics), function ($result, $metricKey) use ($metrics, $group) {
            [$column, $aggregateFunction, $condition] = array_pad($metrics[$metricKey], 3, null);
            $filteredGroup = $this->applyCondition($group, $condition);

            if ($aggregateFunction === 'list') {
                $result[$metricKey] = $filteredGroup->pluck($column)->toArray();
            } else {
                $result[$metricKey] = $filteredGroup->isNotEmpty()
                    ? $filteredGroup->pluck($column)->filter(fn ($value) => is_numeric($value))->$aggregateFunction()
                    : 0;
            }

            return $result;
        }, []);
    }

    /**
     * Prepares a date range, defaulting to the current month if no dates are specified.
     */
    protected function prepareDateRange(?Carbon $startDate, ?Carbon $endDate): array
    {
        return [
            Carbon::parse($startDate ?? Carbon::now()->startOfMonth())->startOfDay(),
            Carbon::parse($endDate ?? Carbon::now()->endOfMonth())->endOfDay(),
        ];
    }

    /**
     * Recursively groups a collection based on an array of keys from a groupBy closure.
     */
    private function recursiveGroupBy(Collection $collection, Closure $groupByClosure, array $metrics): Collection
    {
        return $collection->groupBy(function ($item) use ($groupByClosure) {
            $keys = $groupByClosure($item);

            return is_array($keys) ? $keys[0] ?? 'Unknown' : $keys;
        })->map(function ($group, $key) use ($groupByClosure, $metrics) {
            $sampleItem = $group->first();
            $keys = $groupByClosure($sampleItem);

            if (is_array($keys) && count($keys) > 1) {
                $remainingGroupByClosure = fn ($item) => array_slice($groupByClosure($item), 1);

                return $this->recursiveGroupBy($group, $remainingGroupByClosure, $metrics);
            }

            return $this->processMetricGroup($group, $metrics);
        });
    }

    /**
     * Recursively merges two collections or arrays.
     */
    private function deepMerge(array $base, array $merge): array
    {
        foreach ($merge as $key => $value) {
            if (isset($base[$key])) {
                if (is_array($base[$key]) && is_array($value)) {
                    $base[$key] = $this->deepMerge($base[$key], $value);
                } elseif (is_numeric($base[$key]) && is_numeric($value)) {
                    $base[$key] += $value;
                }
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }
}
