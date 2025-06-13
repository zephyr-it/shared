<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use ZephyrIt\Shared\Helpers\ApplicationHelpers;
use ZephyrIt\Shared\Helpers\ArrayHelpers;
use ZephyrIt\Shared\Helpers\EnumHelpers;
use ZephyrIt\Shared\Helpers\FormatHelpers;
use ZephyrIt\Shared\Helpers\GeneratorHelpers;
use ZephyrIt\Shared\Helpers\InstallHelper;
use ZephyrIt\Shared\Helpers\ModelHelpers;
use ZephyrIt\Shared\Helpers\ModuleHelpers;
use ZephyrIt\Shared\Helpers\StringHelpers;

// ArrayHelpers
if (! function_exists('flattenArray')) {
    function flattenArray(array $array, string $prefix = ''): array
    {
        return ArrayHelpers::flattenArray($array, $prefix);
    }
}

// FormatHelpers
if (! function_exists('numberToIndianFormat')) {
    function numberToIndianFormat($number): string
    {
        return FormatHelpers::numberToIndianFormat($number);
    }
}

if (! function_exists('numberToWord')) {
    function numberToWord(int | float | string $number, string $type = 'spellout', ?string $locale = null, ?string $currency = null): string
    {
        return FormatHelpers::numberToWord($number, $type, $locale, $currency);
    }
}

if (! function_exists('formatNumberShort')) {
    function formatNumberShort(int | float | string $number, int $precision = 1): string
    {
        return FormatHelpers::formatNumberShort($number, $precision);
    }
}

if (! function_exists('formatCopyMessage')) {
    function formatCopyMessage(?string $label): string
    {
        return FormatHelpers::formatCopyMessage($label);
    }
}

if (! function_exists('formatAddButtonLabel')) {
    function formatAddButtonLabel(?string $label = null): string
    {
        return FormatHelpers::formatAddButtonLabel($label);
    }
}

// StringHelpers
if (! function_exists('sanitizeAndFormat')) {
    function sanitizeAndFormat(?string $input, bool $toLowerCase = false, bool $removeSpaces = false, bool $formatWords = true): ?string
    {
        return StringHelpers::sanitizeAndFormat($input, $toLowerCase, $removeSpaces, $formatWords);
    }
}

if (! function_exists('sanitizeSpecialCharacters')) {
    function sanitizeSpecialCharacters(?string $input, string $allowedCharacters = ' '): ?string
    {
        return StringHelpers::sanitizeSpecialCharacters($input, $allowedCharacters);
    }
}

if (! function_exists('normalizeString')) {
    function normalizeString(?string $input): ?string
    {
        return StringHelpers::normalizeString($input);
    }
}

// EnumHelpers
if (! function_exists('getEnumValues')) {
    function getEnumValues(string $enumClass): array
    {
        return EnumHelpers::getEnumValues($enumClass);
    }
}

if (! function_exists('getEnumLabels')) {
    function getEnumLabels(string $enumClass): array
    {
        return EnumHelpers::getEnumLabels($enumClass);
    }
}

if (! function_exists('getFilteredEnumStatuses')) {
    function getFilteredEnumStatuses(string $enumClass, array $excludedCases = []): array
    {
        return EnumHelpers::getFilteredEnumStatuses($enumClass, $excludedCases);
    }
}

// ModelHelpers
if (! function_exists('getAllModelClasses')) {
    function getAllModelClasses(
        array $excludedModules = [],
        array $excludedModels = [],
        array $includedModels = []
    ): array {
        return ModelHelpers::getAllModelClasses($excludedModules, $excludedModels, $includedModels);
    }
}

if (! function_exists('getModelFieldSuggestions')) {
    function getModelFieldSuggestions(?string $modelClass): array
    {
        return ModelHelpers::getModelFieldSuggestions($modelClass);
    }
}

if (! function_exists('resolveFieldCast')) {
    function resolveFieldCast(string $modelClass, string $field): ?string
    {
        return ModelHelpers::resolveFieldCast($modelClass, $field);
    }
}

if (! function_exists('getCastSuggestions')) {
    function getCastSuggestions(string $modelClass, ?string $field): array
    {
        return ModelHelpers::getCastSuggestions($modelClass, $field);
    }
}

if (! function_exists('getFormatOptionsForCast')) {
    function getFormatOptionsForCast(string $cast): array
    {
        return ModelHelpers::getFormatOptionsForCast($cast);
    }
}

if (! function_exists('getLastRecord')) {
    function getLastRecord($model, string $columnName, ?string $formattedDate = null, string $prefix = '')
    {
        return ModelHelpers::getLastRecord($model, $columnName, $formattedDate, $prefix);
    }
}

// GeneratorHelpers
if (! function_exists('getClassesFromDirectory')) {
    function getClassesFromDirectory(string $directory, string $namespace, array $excludeClasses = []): array
    {
        return GeneratorHelpers::getClassesFromDirectory($directory, $namespace, $excludeClasses);
    }
}

if (! function_exists('generateUniqueNumber')) {
    function generateUniqueNumber($model, string $prefix, string $columnName, $customDate = null, string $dateFormat = 'Y/m'): string
    {
        return GeneratorHelpers::generateUniqueNumber($model, $prefix, $columnName, $customDate, $dateFormat);
    }
}

if (! function_exists('generateYears')) {
    function generateYears(int $startYear = 1900, ?int $endYear = null): array
    {
        return GeneratorHelpers::generateYears($startYear, $endYear);
    }
}

// ApplicationHelpers
if (! function_exists('getDenominationsArray')) {
    function getDenominationsArray(string $type): array
    {
        return ApplicationHelpers::getDenominationsArray($type);
    }
}

if (! function_exists('getCurrencySymbol')) {
    function getCurrencySymbol(): string
    {
        return ApplicationHelpers::getCurrencySymbol();
    }
}

if (! function_exists('documentDetails')) {
    function documentDetails(): array
    {
        return ApplicationHelpers::documentDetails();
    }
}

if (! function_exists('transformSupportedLocales')) {
    function transformSupportedLocales(): array
    {
        return ApplicationHelpers::transformSupportedLocales();
    }
}

if (! function_exists('safeTenant')) {
    function safeTenant()
    {
        return ApplicationHelpers::safeTenant();
    }
}

// Module Helpers
if (! function_exists('getActiveModules')) {
    function getActiveModules(): array
    {
        return ModuleHelpers::getActiveModules();
    }
}

if (! function_exists('getAllModulePaths')) {
    function getAllModulePaths(): array
    {
        return ModuleHelpers::getAllModulePaths();
    }
}

if (! function_exists('getModuleModels')) {
    function getModuleModels(string $moduleName): array
    {
        return ModuleHelpers::getModuleModels($moduleName);
    }
}

if (! function_exists('getAllModelsAcrossModules')) {
    function getAllModelsAcrossModules(array $excludedModules = []): array
    {
        return ModuleHelpers::getAllModelsAcrossModules($excludedModules);
    }
}

if (! function_exists('moduleExists')) {
    function moduleExists(string $moduleName): bool
    {
        return ModuleHelpers::moduleExists($moduleName);
    }
}

if (! function_exists('getModuleConfig')) {
    function getModuleConfig(string $moduleName, string $key, $default = null)
    {
        return ModuleHelpers::getModuleConfig($moduleName, $key, $default);
    }
}

if (! function_exists('getModuleNamespace')) {
    function getModuleNamespace(string $moduleName): string
    {
        return ModuleHelpers::getModuleNamespace($moduleName);
    }
}

if (! function_exists('clearModuleCache')) {
    function clearModuleCache(): void
    {
        ModuleHelpers::clearCache();
    }
}

if (! function_exists('clearSpecificModuleCache')) {
    function clearSpecificModuleCache(string $moduleName): void
    {
        ModuleHelpers::clearModuleCache($moduleName);
    }
}

if (! function_exists('clearModelCache')) {
    function clearModelCache(): void
    {
        ModelHelpers::clearCache();
    }
}

if (! function_exists('clearSpecificModelCache')) {
    function clearSpecificModelCache(string $modelClass): void
    {
        ModelHelpers::clearModelCache($modelClass);
    }
}

if (! function_exists('clearAppCaches')) {
    function clearAppCaches(): void
    {
        clearModuleCache();
        clearModelCache();
    }
}

// InstallHelper
if (! function_exists('install_publish')) {
    function install_publish(Command $command, array $items, bool $force = false): void
    {
        InstallHelper::publish($command, $items, $force);
    }
}

if (! function_exists('install_publish_migrations')) {
    function install_publish_migrations(
        Command $command,
        string $sourceDir,
        ?string $targetDir = null,
        string $type = 'settings',
        bool $timestamp = true,
        ?array $migrationList = null,
        bool $force = false
    ): void {
        InstallHelper::publishMigrations(
            command: $command,
            sourceDir: $sourceDir,
            targetDir: $targetDir,
            type: $type,
            timestamp: $timestamp,
            migrationList: $migrationList,
            force: $force
        );
    }
}

if (! function_exists('install_publish_files')) {
    function install_publish_files(
        Command $command,
        string $sourceDir,
        array $map = [
            'config' => 'config_path',
            'public' => 'public_path',
        ]
    ): void {
        InstallHelper::publishFiles($command, $sourceDir, $map);
    }
}
