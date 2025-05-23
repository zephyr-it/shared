<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Helpers;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ModuleHelpers
{
    /**
     * Cache duration in seconds.
     */
    private static int $cacheDuration = 3600; // 1 hour

    /**
     * Cache key prefix.
     */
    private static string $cachePrefix = 'module_helpers_';

    /**
     * Cache tags for grouping cache entries.
     */
    private static string $cacheTag = 'module_helpers';

    /**
     * Clear all module helper caches.
     */
    public static function clearCache(): void
    {
        try {
            if (self::supportsTag()) {
                Cache::tags([self::$cacheTag])->flush();

                return;
            }

            // Fallback for drivers that don't support tags
            $keys = [
                self::$cachePrefix . 'active_modules',
                self::$cachePrefix . 'all_module_paths',
            ];

            foreach ($keys as $key) {
                Cache::forget($key);
            }

            // Clear module-specific caches
            foreach (self::getAllModulePaths() as $modulePath) {
                $moduleName = strtolower(basename($modulePath));
                Cache::forget(self::$cachePrefix . 'module_models_' . $moduleName);
                Cache::forget(self::$cachePrefix . 'module_exists_' . $moduleName);
            }
        } catch (Exception $e) {
            logger()->error('Error clearing module helper caches: ' . $e->getMessage());
        }
    }

    /**
     * Clear cache for a specific module.
     */
    public static function clearModuleCache(string $moduleName): void
    {
        try {
            $moduleName = strtolower($moduleName);

            if (self::supportsTag()) {
                // If we support tags, we can be more selective by using a module-specific tag
                // This would require updating the caching mechanism to use module-specific tags
                Cache::tags([self::$cacheTag])->flush();

                return;
            }

            // Fallback for drivers that don't support tags
            Cache::forget(self::$cachePrefix . 'module_models_' . $moduleName);
            Cache::forget(self::$cachePrefix . 'module_exists_' . $moduleName);

            // Also clear general module caches as they might include this module's data
            Cache::forget(self::$cachePrefix . 'active_modules');
            Cache::forget(self::$cachePrefix . 'all_module_paths');
            Cache::forget(self::$cachePrefix . 'all_models_' . md5(serialize([])));
        } catch (Exception $e) {
            logger()->error("Error clearing cache for module {$moduleName}: " . $e->getMessage());
        }
    }

    /**
     * Get all active module names.
     */
    public static function getActiveModules(): array
    {
        return self::getCache()->remember(self::$cachePrefix . 'active_modules', self::$cacheDuration, function () {
            try {
                $moduleStatuses = json_decode(File::get(base_path('modules_statuses.json')), true);

                return array_keys(array_filter($moduleStatuses, fn ($status) => $status === true));
            } catch (Exception $e) {
                logger()->error('Error reading modules_statuses.json: ' . $e->getMessage());

                return [];
            }
        });
    }

    /**
     * Get all models across all modules.
     */
    public static function getAllModelsAcrossModules(array $excludedModules = []): array
    {
        $cacheKey = self::$cachePrefix . 'all_models_' . md5(serialize($excludedModules));

        return self::getCache()->remember($cacheKey, self::$cacheDuration, function () use ($excludedModules) {
            try {
                $allModels = [];
                $modules = self::getAllModulePaths();

                foreach ($modules as $modulePath) {
                    $moduleName = basename($modulePath);

                    if (in_array($moduleName, $excludedModules)) {
                        continue;
                    }

                    $models = self::getModuleModels($moduleName);
                    $allModels = array_merge($allModels, $models);
                }

                return $allModels;
            } catch (Exception $e) {
                logger()->error('Error reading models across modules: ' . $e->getMessage());

                return [];
            }
        });
    }

    /**
     * Get all module paths.
     */
    public static function getAllModulePaths(): array
    {
        return self::getCache()->remember(self::$cachePrefix . 'all_module_paths', self::$cacheDuration, function () {
            try {
                if (! File::exists(base_path('Modules'))) {
                    return [];
                }

                return File::directories(base_path('Modules'));
            } catch (Exception $e) {
                logger()->error('Error reading modules directory: ' . $e->getMessage());

                return [];
            }
        });
    }

    /**
     * Get module config value with a shorter cache time for configuration.
     */
    public static function getModuleConfig(string $moduleName, string $key, $default = null)
    {
        // Use a shorter cache duration for configuration (10 minutes)
        $configCacheDuration = 600;

        $cacheKey = self::$cachePrefix . 'module_config_' . strtolower($moduleName) . '_' . str_replace('.', '_', $key);

        return self::getCache()->remember($cacheKey, $configCacheDuration, function () use ($moduleName, $key, $default) {
            try {
                $moduleConfigPath = base_path("Modules/{$moduleName}/config/config.php");

                if (! File::exists($moduleConfigPath)) {
                    return $default;
                }

                $config = require $moduleConfigPath;

                return data_get($config, $key, $default);
            } catch (Exception $e) {
                logger()->error("Error reading config for module {$moduleName}: " . $e->getMessage());

                return $default;
            }
        });
    }

    /**
     * Get module models with their full namespaces.
     */
    public static function getModuleModels(string $moduleName): array
    {
        $cacheKey = self::$cachePrefix . 'module_models_' . strtolower($moduleName);

        return self::getCache()->remember($cacheKey, self::$cacheDuration, function () use ($moduleName) {
            try {
                $modelsPath = base_path("Modules/{$moduleName}/app/Models");

                if (! File::exists($modelsPath)) {
                    return [];
                }

                return collect(File::files($modelsPath))
                    ->filter(fn ($file) => $file->getExtension() === 'php')
                    ->map(function ($file) use ($moduleName) {
                        $className = $file->getFilenameWithoutExtension();

                        return "Modules\\{$moduleName}\\Models\\{$className}";
                    })
                    ->toArray();
            } catch (Exception $e) {
                logger()->error("Error reading models for module {$moduleName}: " . $e->getMessage());

                return [];
            }
        });
    }

    /**
     * Generate the PSR-4 namespace for a module.
     */
    public static function getModuleNamespace(string $moduleName): string
    {
        return "Modules\\{$moduleName}\\";
    }

    /**
     * Check if a module exists.
     */
    public static function moduleExists(string $moduleName): bool
    {
        $cacheKey = self::$cachePrefix . 'module_exists_' . strtolower($moduleName);

        return self::getCache()->remember($cacheKey, self::$cacheDuration, function () use ($moduleName) {
            try {
                return File::exists(base_path("Modules/{$moduleName}"));
            } catch (Exception $e) {
                logger()->error("Error checking if module {$moduleName} exists: " . $e->getMessage());

                return false;
            }
        });
    }

    /**
     * Get tagged cache instance.
     */
    private static function getCache()
    {
        if (self::supportsTag()) {
            return Cache::tags([self::$cacheTag]);
        }

        return Cache::driver();
    }

    /**
     * Cache driver supports tags.
     */
    private static function supportsTag(): bool
    {
        $driver = config('cache.default');

        return in_array($driver, ['redis', 'memcached']);
    }
}
