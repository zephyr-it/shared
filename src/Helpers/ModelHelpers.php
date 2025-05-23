<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Helpers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionMethod;
use Throwable;

class ModelHelpers
{
    /**
     * Cache duration in seconds.
     */
    private static int $cacheDuration = 3600; // 1 hour

    /**
     * Cache key prefix.
     */
    private static string $cachePrefix = 'model_helpers_';

    /**
     * Cache tag for model helpers.
     */
    private static string $cacheTag = 'model_helpers';

    /**
     * Clear all model helper caches.
     */
    public static function clearCache(): void
    {
        try {
            if (self::supportsTag()) {
                Cache::tags([self::$cacheTag])->flush();

                return;
            }

            // For drivers that don't support tags, we use a simple approach
            Cache::flush();
        } catch (Throwable $e) {
            logger()->error("Error clearing model helper caches: {$e->getMessage()}");
        }
    }

    /**
     * Clear cache for a specific model.
     */
    public static function clearModelCache(string $modelClass): void
    {
        try {
            $modelHash = md5($modelClass);

            if (self::supportsTag()) {
                // If we had model-specific tags, we could be more selective here
                Cache::tags([self::$cacheTag])->flush();

                return;
            }

            // Forget model-specific caches
            self::getCache()->forget(self::$cachePrefix . 'field_suggestions_' . $modelHash);

            // Also clear any caches that might reference this model
            self::getCache()->forget(self::$cachePrefix . 'all_classes_' . md5(serialize([[], [], []])));
        } catch (Throwable $e) {
            logger()->error("Error clearing cache for model {$modelClass}: {$e->getMessage()}");
        }
    }

    /**
     * Get all model classes from app and module directories.
     *
     * @param  array  $excludedModules  Namespace segments to skip (e.g. ['Telescope'])
     * @param  array  $excludedModels  Full class names to exclude
     * @param  array  $includedModels  Optional: Return only these classes
     * @return array [FullyQualifiedClass => ClassName]
     */
    public static function getAllModelClasses(
        array $excludedModules = [],
        array $excludedModels = [],
        array $includedModels = []
    ): array {
        $cacheKey = self::$cachePrefix . 'all_classes_' . md5(serialize([$excludedModules, $excludedModels, $includedModels]));

        return self::getCache()->remember($cacheKey, self::$cacheDuration, function () use ($excludedModules, $excludedModels, $includedModels) {
            try {
                // Get app models
                $appModels = self::getAppModels($excludedModels, $includedModels);

                // Get module models using ModuleHelpers
                $moduleModels = self::getModuleModels($excludedModules, $excludedModels, $includedModels);

                // Merge and format results
                return collect($appModels)
                    ->merge($moduleModels)
                    ->sort()
                    ->mapWithKeys(fn ($class) => [$class => class_basename($class)])
                    ->toArray();
            } catch (Exception $e) {
                logger()->error('Error getting all model classes: ' . $e->getMessage());

                return [];
            }
        });
    }

    /**
     * Get cast suggestions based on the field type.
     */
    public static function getCastSuggestions(string $modelClass, ?string $field): array
    {
        if (! $field || ! class_exists($modelClass)) {
            return [];
        }

        $cacheKey = self::$cachePrefix . 'cast_suggestions_' . md5($modelClass . '_' . ($field ?? 'null'));

        return self::getCache()->remember($cacheKey, self::$cacheDuration, function () use ($modelClass, $field) {
            try {
                $cast = self::resolveFieldCast($modelClass, $field);

                return match ($cast) {
                    'boolean' => [
                        'true' => 'true',
                        'false' => 'false',
                        '1' => '1',
                        '0' => '0',
                    ],
                    'date', 'datetime' => [
                        now()->toDateString() => now()->toDateString(),
                        now()->addDay()->toDateString() => now()->addDay()->toDateString(),
                        now()->subDay()->toDateString() => now()->subDay()->toDateString(),
                    ],
                    'array', 'json' => [
                        'item1, item2, item3' => 'item1, item2, item3',
                    ],
                    default => [],
                };
            } catch (Throwable $e) {
                logger()->error("Error getting cast suggestions for {$field} on model {$modelClass}: {$e->getMessage()}");

                return [];
            }
        });
    }

    /**
     * Get format options for a specific cast type.
     *
     * Provides display format options for various Eloquent cast types to be used in UI components.
     * The array keys are format identifiers, values are human-readable descriptions.
     *
     * @param  string  $cast  The cast type from model's $casts array
     * @return array [format => description] pairs for the given cast type
     *
     * @example For 'date' cast:
     * [
     *   'Y-m-d' => 'YYYY-MM-DD',
     *   'd-m-Y' => 'DD-MM-YYYY',
     *   // etc...
     * ]
     */
    public static function getFormatOptionsForCast(string $cast): array
    {
        $normalizedCast = fn (string $cast) => match (true) {
            str_starts_with($cast, 'date:') => 'date',
            str_starts_with($cast, 'datetime:') => 'datetime',
            str_starts_with($cast, 'decimal:'),
            str_starts_with($cast, 'float:'),
            str_starts_with($cast, 'double:') => 'decimal',
            default => $cast,
        };

        return match ($normalizedCast($cast)) {
            'date' => [
                'Y-m-d' => 'YYYY-MM-DD (2025-01-31)',
                'd/m/Y' => 'DD/MM/YYYY (31/01/2025)',
                'd M Y' => 'Pretty (31 Jan 2025)',
                'M j, Y' => 'Long (Jan 1, 2025)',
                'Y-m-d H:i' => '24-Hour (2025-01-31 13:45)',
                'd-m-Y h:i A' => '12-Hour (31-01-2025 01:45 PM)',
                'relative' => 'Relative Time (e.g., 3 hours ago)',
                'start_of_day' => 'Start of Day (00:00)',
                'end_of_day' => 'End of Day (23:59)',
            ],
            'datetime' => [
                'Y-m-d H:i' => '24-Hour (YYYY-MM-DD HH:MM)',
                'd-m-Y h:i A' => '12-Hour (DD-MM-YYYY hh:mm AM/PM)',
                'd M Y, h:i A' => 'Pretty: 01 Jan 2025, 2:30 PM',
                'relative' => 'Relative (e.g., 3 hours ago)',
            ],
            'boolean' => [
                'yes_no' => 'Yes / No',
                'true_false' => 'True / False',
                '1_0' => '1 / 0',
                'checkmark' => '✔️ / ❌',
            ],
            'int', 'integer', 'float', 'double', 'decimal' => [
                'raw' => 'Raw Number',
                'number' => 'Formatted Number (1,000.00)',
                'currency' => 'Currency (₹1,000.00)',
                'percentage' => 'Percentage (85%)',
                'round' => 'Rounded Integer',
            ],
            'array', 'json' => [
                'csv' => 'Comma Separated (item1, item2)',
                'newline' => 'Each on New Line',
                'json' => 'JSON String',
                'count' => 'Item Count (e.g., 3 items)',
            ],
            'string' => [
                'ucwords' => 'Title Case',
                'upper' => 'UPPERCASE',
                'lower' => 'lowercase',
                'slug' => 'Slug (e.g. hello world → hello-world)',
                'truncate_50' => 'Truncate (50 chars)',
            ],
            'enum' => [
                'label' => 'Enum Label (getLabel())',
                'value' => 'Enum Value',
                'slug' => 'Slugified Label (e.g., active → active)',
            ],
            default => [
                'string' => 'As Text',
            ],
        };
    }

    /**
     * Get the last record based on a custom date format in a specified column.
     * Applies `withTrashed` only if the model uses `SoftDeletes` trait.
     */
    public static function getLastRecord($model, string $columnName, ?string $formattedDate = null, string $prefix = '')
    {
        try {
            $query = $model::query()->orderByDesc('id');

            if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model))) {
                $query->withTrashed();
            }

            if ($formattedDate) {
                $query->where($columnName, 'like', "{$prefix}-{$formattedDate}-%");
            }

            return $query->first();
        } catch (Throwable $e) {
            logger()->error('Error getting last record for model ' . get_class($model) . ": {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Get all field suggestions for a given model class,
     * including dot-notated relations (e.g. customer.name, agent.mobile).
     */
    public static function getModelFieldSuggestions(?string $modelClass): array
    {
        // 🛡 Skip if not a valid model class
        if (! $modelClass || ! class_exists($modelClass)) {
            return [];
        }

        // 🔐 Cache key for this model's suggestions
        $cacheKey = self::$cachePrefix . 'field_suggestions_' . md5($modelClass);

        // 📦 Cache the computed field suggestions
        return self::getCache()->remember($cacheKey, self::$cacheDuration, function () use ($modelClass) {
            try {
                /** @var Model $model */
                $model = app($modelClass)->newInstance(); // 💡 Safe, non-persisted blank model

                // 📦 Start with basic fillable + primary timestamps
                $directFields = collect($model->getFillable())
                    ->merge(['id', 'created_at', 'updated_at']);

                // 🧠 Add accessor methods like getBalanceAttribute → balance
                $accessors = collect(get_class_methods($model))
                    ->filter(fn ($method) => str_starts_with($method, 'get') && str_ends_with($method, 'Attribute'))
                    ->map(fn ($method) => Str::snake(str_replace(['get', 'Attribute'], '', $method)));

                // ➕ Add explicitly appended attributes
                $appends = collect($model->getAppends());

                // 🔁 Combine all direct model properties
                $fields = $directFields
                    ->merge($accessors)
                    ->merge($appends)
                    ->unique();

                // 📡 Collect relation-derived fields (e.g. agent.name → agent.name)
                $relationFields = collect();

                // 🛑 Skip these methods from being called
                $blacklist = [
                    '__toString', 'push', 'pushQuietly', 'restore', 'deletePreservingMedia',
                    'activities', 'updateTimestamps', 'connection', 'media',
                    'attributesToArray', 'freshTimestampString', 'getDateFormat',
                    'handlePolicyTransactions', 'deleteRelatedModels', 'restoreRelatedModels',
                ];

                // 🧭 Inspect class methods to find valid relations
                foreach (get_class_methods($model) as $method) {
                    if (in_array($method, $blacklist)) {
                        continue;
                    }

                    try {
                        $reflection = new ReflectionMethod($model, $method);

                        if (
                            $reflection->getNumberOfParameters() === 0 &&
                            $reflection->isPublic() &&
                            ! $reflection->isStatic() &&
                            $reflection->getDeclaringClass()->getName() === $modelClass // skip inherited traits/methods
                        ) {
                            // ✅ Optionally restrict to methods with explicit relation return types
                            $returnType = $reflection->getReturnType();

                            if (
                                $returnType &&
                                is_subclass_of((string) $returnType, \Illuminate\Database\Eloquent\Relations\Relation::class)
                            ) {
                                $relation = $model->$method();

                                if ($relation instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                                    $related = $relation->getRelated();

                                    $relatedFields = method_exists($related, 'getFillable')
                                        ? $related->getFillable()
                                        : [];

                                    foreach ($relatedFields as $field) {
                                        $relationFields->push("{$method}.{$field}");
                                    }
                                }
                            }
                        }
                    } catch (Throwable $e) {
                        // 🛑 Skip broken or non-relational methods gracefully
                        logger()->error("Skipping relation method {$method} on {$modelClass}: {$e->getMessage()}");
                    }
                }

                // 🧾 Final map: key = value (same), sorted and unique
                return $fields
                    ->merge($relationFields)
                    ->unique()
                    ->sort()
                    ->mapWithKeys(fn ($field) => [$field => $field])
                    ->toArray();
            } catch (Throwable $e) {
                logger()->error("Error getting field suggestions for model {$modelClass}: {$e->getMessage()}");

                return [];
            }
        });
    }

    /**
     * Resolve the cast type of a field in a model.
     */
    public static function resolveFieldCast(string $modelClass, string $field): ?string
    {
        if (! class_exists($modelClass)) {
            return null;
        }

        $cacheKey = self::$cachePrefix . 'field_cast_' . md5($modelClass . '_' . $field);

        return self::getCache()->remember($cacheKey, self::$cacheDuration, function () use ($modelClass, $field) {
            try {
                $model = new $modelClass;

                $parts = explode('.', $field);

                foreach ($parts as $i => $part) {
                    if (! isset($model)) {
                        break;
                    }

                    if ($i === array_key_last($parts)) {
                        return $model->getCasts()[$part] ?? null;
                    }

                    if (method_exists($model, $part)) {
                        $model = $model->{$part}()->getRelated();
                    } else {
                        return null;
                    }
                }
            } catch (Throwable $e) {
                logger()->error("Error resolving field cast for {$field} on model {$modelClass}: {$e->getMessage()}");

                return null;
            }

            return null;
        });
    }

    /**
     * Get models from the app directory.
     */
    private static function getAppModels(array $excludedModels = [], array $includedModels = []): array
    {
        try {
            $path = app_path('Models');
            $models = collect();

            if (! File::exists($path)) {
                return [];
            }

            $files = File::allFiles($path);

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $className = $file->getFilenameWithoutExtension();
                $fullClass = "App\\Models\\{$className}";

                if (in_array($fullClass, $excludedModels, true)) {
                    continue;
                }

                if (! empty($includedModels) && ! in_array($fullClass, $includedModels, true)) {
                    continue;
                }

                if (class_exists($fullClass) && is_subclass_of($fullClass, Model::class)) {
                    $models->push($fullClass);
                }
            }

            return $models->toArray();
        } catch (Exception $e) {
            logger()->error('Error getting app models: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get cache instance with tag if supported.
     */
    private static function getCache()
    {
        if (self::supportsTag()) {
            return Cache::tags([self::$cacheTag]);
        }

        return Cache::driver();
    }

    /**
     * Get models from all modules.
     */
    private static function getModuleModels(
        array $excludedModules = [],
        array $excludedModels = [],
        array $includedModels = []
    ): array {
        try {
            $allModuleModels = [];

            // Get all modules' paths
            $modulePaths = ModuleHelpers::getAllModulePaths();

            foreach ($modulePaths as $modulePath) {
                $moduleName = basename($modulePath);

                // Skip excluded modules
                if (in_array($moduleName, $excludedModules, true)) {
                    continue;
                }

                // Get models for this module
                $moduleModels = ModuleHelpers::getModuleModels($moduleName);

                // Filter models
                $filteredModels = collect($moduleModels)
                    ->filter(function ($modelClass) use ($excludedModels, $includedModels) {
                        // Skip excluded models
                        if (in_array($modelClass, $excludedModels, true)) {
                            return false;
                        }

                        // Include only specific models if provided
                        if (! empty($includedModels) && ! in_array($modelClass, $includedModels, true)) {
                            return false;
                        }

                        // Check if class exists and is a model
                        return class_exists($modelClass) && is_subclass_of($modelClass, Model::class);
                    })
                    ->toArray();

                $allModuleModels = array_merge($allModuleModels, $filteredModels);
            }

            return $allModuleModels;
        } catch (Exception $e) {
            logger()->error('Error getting module models: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Check if cache driver supports tags.
     */
    private static function supportsTag(): bool
    {
        $driver = config('cache.default');

        return in_array($driver, ['redis', 'memcached']);
    }
}
