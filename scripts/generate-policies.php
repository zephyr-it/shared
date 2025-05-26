#!/usr/bin/env php
<?php

$options = getopt('', ['force', 'path:']);
$force = isset($options['force']);

$rawPath = $options['path'] ?? getcwd();
$basePath = realpath($rawPath);

if (! $basePath || ! is_dir($basePath)) {
    fwrite(STDERR, "❌ Invalid path: {$rawPath}\n");
    exit(1);
}

/**
 * Convert PascalCase class name to kebab::case::resource for permissions
 */
function kebabToPermission(string $name): string
{
    return strtolower(preg_replace('/(?<!^)([A-Z])/', '::$1', $name));
}

/**
 * Get correct namespace for Policy by replacing Models with Policies in the Model file's namespace
 */
function getPolicyNamespaceFromModel(string $modelFile): string
{
    $contents = file_get_contents($modelFile);

    if (preg_match('/^namespace\s+(.+?)\\\Models\s*;/m', $contents, $matches)) {
        return $matches[1] . '\\Policies';
    }

    return 'ZephyrIt\\Unknown\\Policies';
}

/**
 * Get the directory path where the Policy should live, based on the model
 */
function getPolicyPathFromModel(string $modelPath): string
{
    return str_replace('/Models', '/Policies', dirname($modelPath));
}

/**
 * Scan recursively for all Models directories
 */
$directories = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($basePath),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($directories as $dir) {
    if (! $dir->isDir() || basename($dir) !== 'Models') {
        continue;
    }

    $modelsDir = $dir->getPathname();

    foreach (glob($modelsDir . '/*.php') as $modelFile) {
        $modelName = pathinfo($modelFile, PATHINFO_FILENAME);
        $policyName = $modelName . 'Policy';

        $policyDir = getPolicyPathFromModel($modelFile);
        $policyFile = $policyDir . '/' . $policyName . '.php';

        if (! is_dir($policyDir)) {
            mkdir($policyDir, 0777, true);
        }

        if (file_exists($policyFile) && ! $force) {
            echo "⏭️  Skipped (exists): $policyFile\n";

            continue;
        }

        $namespace = getPolicyNamespaceFromModel($modelFile);
        $resource = kebabToPermission($modelName);

        $stub = <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use ZephyrIt\\Shared\\Policies\\BasePolicy;

class {$policyName} extends BasePolicy
{
    protected string \$resource = '{$resource}';

    // protected array \$permissions = [
    //     'reorder' => 'custom_permission_key',
    // ];

    // protected array \$abilities = [
    //     'viewAny', 'view', 'create', 'update', 'delete',
    //     'deleteAny', 'forceDelete', 'forceDeleteAny',
    //     'restore', 'restoreAny', 'replicate', 'reorder',
    // ];
}
PHP;

        file_put_contents($policyFile, $stub);
        echo "✅ Created: $policyFile\n";
    }
}

echo "\n🎯 Done scanning and generating policies inside: {$basePath}\n";
