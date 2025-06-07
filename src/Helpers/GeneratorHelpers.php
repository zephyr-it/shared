<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Helpers;

use Carbon\Carbon;
use RuntimeException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class GeneratorHelpers
{
    /**
     * Generate a unique number for a model with retry counter increment.
     */
    public static function generateUniqueNumber($model, string $prefix, string $columnName, $customDate = null, string $dateFormat = 'Y/m'): string
    {
        $maxRetries = 10;
        $retryDelay = 50000;
        $lockKey = "lock:unique_number:{$model}:{$prefix}";

        $date = $customDate ? Carbon::parse($customDate) : Carbon::now();
        $formattedDate = $date->format($dateFormat);

        $lastCounter = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            if (Redis::set($lockKey, Str::random(), 'NX', 'EX', 3)) {
                try {
                    if (is_null($lastCounter)) {
                        $lastRecord = ModelHelpers::getLastRecord($model, $columnName, $formattedDate, $prefix);
                        $lastCounter = $lastRecord
                            ? (int) substr($lastRecord->$columnName, strlen($prefix) + strlen($formattedDate) + 2)
                            : 0;
                    }

                    $newCounter = $lastCounter + $attempt;
                    $counterLength = max(4, strlen((string) $newCounter));
                    $counterString = str_pad((string) $newCounter, $counterLength, '0', STR_PAD_LEFT);
                    $newNumber = sprintf('%s-%s-%s', $prefix, $formattedDate, $counterString);

                    if (! $model::where($columnName, $newNumber)->exists()) {
                        return $newNumber;
                    }

                    logger()->warning("Duplicate unique number detected: {$newNumber}, retrying...");
                } finally {
                    Redis::del($lockKey);
                }
            } else {
                logger()->info('Waiting for lock to generate unique number...');
            }

            usleep($retryDelay);
        }

        throw new RuntimeException('Unable to generate unique number after maximum retries.');
    }

    /**
     * Generate an array of years from a given start to the current year.
     */
    public static function generateYears(int $startYear = 1900, ?int $endYear = null): array
    {
        $endYear = $endYear ?? (int) date('Y');

        return array_combine(range($endYear, $startYear), range($endYear, $startYear));
    }

    /**
     * Get all class names from a directory.
     */
    public static function getClassesFromDirectory(string $directory, string $namespace, array $excludeClasses = []): array
    {
        $filesystem = new Filesystem;
        $files = $filesystem->allFiles($directory);
        $classes = [];

        foreach ($files as $file) {
            $path = $file->getRelativePathname();
            $class = $namespace . '\\' . str_replace(['/', '.php'], ['\\', ''], $path);

            if (class_exists($class) && ! in_array($class, $excludeClasses)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
