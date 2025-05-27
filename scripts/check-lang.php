#!/usr/bin/env php
<?php

/**
 * check-lang-fixed.php
 * ----------------------------
 * A standalone PHP script to extract translation keys and generate proper nested language files.
 */
function snakeCase($string)
{
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
}

function titleCase($string)
{
    return ucwords(str_replace(['_', '.'], [' ', ' '], $string));
}

function setNested(array &$array, string $key, $value)
{
    $segments = explode('.', $key);
    while (count($segments) > 1) {
        $segment = array_shift($segments);
        if (! isset($array[$segment]) || ! is_array($array[$segment])) {
            $array[$segment] = [];
        }
        $array = &$array[$segment];
    }
    $array[array_shift($segments)] = $value;
}

function arrayToString(array $array, int $level = 1): string
{
    $indent = str_repeat('    ', $level);
    $output = '';

    foreach ($array as $key => $value) {
        $output .= $indent . "'" . addslashes($key) . "' => ";

        if (is_array($value)) {
            $output .= '[
' . arrayToString($value, $level + 1) . $indent . '],
';
        } else {
            $output .= "'" . addslashes($value) . "',
";
        }
    }

    return $output;
}

function recursiveKsort(array &$array): void
{
    ksort($array);
    foreach ($array as &$value) {
        if (is_array($value)) {
            recursiveKsort($value);
        }
    }
}

function getTranslationKeysFromFile($content)
{
    $patterns = [
        "/__\(['\"]([^'\"]+)['\"]\)/",
        "/trans\(['\"]([^'\"]+)['\"]\)/",
        "/@lang\(['\"]([^'\"]+)['\"]\)/",
        "/Lang::get\(['\"]([^'\"]+)['\"]\)/",
    ];

    $matches = [];

    foreach ($patterns as $pattern) {
        preg_match_all($pattern, $content, $found);
        if (! empty($found[1])) {
            $matches = array_merge($matches, $found[1]);
        }
    }

    return array_unique(array_filter($matches, function ($key) {
        return strpos($key, '{$') === false;
    }));
}

function recursiveScanForKeys($paths)
{
    $allKeys = [];

    foreach ($paths as $path) {
        if (! is_dir($path)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if (! in_array($ext, ['php', 'blade.php'])) {
                continue;
            }

            $content = file_get_contents($file->getRealPath());
            $keys = getTranslationKeysFromFile($content);

            foreach ($keys as $key) {
                $allKeys[] = $key;
            }
        }
    }

    return array_unique($allKeys);
}

function saveLangFile($filePath, array $array)
{
    recursiveKsort($array);
    $content = '<?php

return [
' . arrayToString($array) . '];
';
    file_put_contents($filePath, $content);
}

function mergeLangArray($existing, $new)
{
    foreach ($new as $k => $v) {
        if (! isset($existing[$k])) {
            $existing[$k] = $v;
        } elseif (is_array($v) && is_array($existing[$k])) {
            $existing[$k] = mergeLangArray($existing[$k], $v);
        }
    }

    return $existing;
}

// === Script starts ===

$baseDir = dirname(__DIR__); // assumes /scripts/check-lang-fixed.php
$package = $argv[1] ?? basename($baseDir);

echo "🔍 Checking language keys for package: {$package}
";

$srcPaths = [
    "{$baseDir}/src",
    "{$baseDir}/resources/views",
];

$langDir = "{$baseDir}/resources/lang/en";
if (! is_dir($langDir)) {
    mkdir($langDir, 0755, true);
}

$logFile = "{$baseDir}/logs/missing_labels.log";
if (! is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}
file_put_contents($logFile, '');

$allKeys = recursiveScanForKeys($srcPaths);
$organized = [];

foreach ($allKeys as $fullKey) {
    if (strpos($fullKey, '::') !== false) {
        [, $fullKey] = explode('::', $fullKey, 2);
    }

    if (strpos($fullKey, '.') === false) {
        continue;
    }

    [$file, $sub] = explode('.', $fullKey, 2);
    $title = titleCase($sub);
    if (! isset($organized[$file])) {
        $organized[$file] = [];
    }
    setNested($organized[$file], $sub, $title);
}

foreach ($organized as $file => $items) {
    $filePath = "{$langDir}/{$file}.php";

    $existing = file_exists($filePath) ? include $filePath : [];
    $merged = mergeLangArray($existing, $items);

    saveLangFile($filePath, $merged);

    echo "✅ Updated: {$file}.php
";
}

echo "🎉 Language check completed. Labels written to {$langDir}
";
