<?php

$packageRoot = dirname(__DIR__);

$sourceDirs = glob($packageRoot . '/resources/lang-*');
$outputDir = $packageRoot . '/resources/lang/en';

if (! is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$mergedFiles = [];

foreach ($sourceDirs as $dir) {
    foreach (glob($dir . '/en/*.php') as $file) {
        $filename = basename($file);
        $data = include $file;

        if (! is_array($data)) {
            continue;
        }

        if (! isset($mergedFiles[$filename])) {
            $mergedFiles[$filename] = [];
        }

        $mergedFiles[$filename] = array_merge_recursive_distinct($mergedFiles[$filename], $data);
    }
}

// Sort keys alphabetically and write to output
foreach ($mergedFiles as $filename => $data) {
    ksort_recursive($data);
    $outputPath = "{$outputDir}/{$filename}";
    $content = export_php_array($data);
    file_put_contents($outputPath, $content);
    echo "✅ Merged: $outputPath\n";
}

echo "\n✅ All lang files have been merged to: resources/lang/en/\n";

// 🔥 Optional Cleanup
if (confirm('Do you want to delete all resources/lang-* folders after merge?', false)) {
    foreach ($sourceDirs as $dir) {
        rrmdir($dir);
        echo "🗑️ Deleted: $dir\n";
    }
}

//
// Helpers
//

function array_merge_recursive_distinct(array &$array1, array &$array2): array
{
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

function ksort_recursive(&$array): void
{
    ksort($array);
    foreach ($array as &$value) {
        if (is_array($value)) {
            ksort_recursive($value);
        }
    }
}

function rrmdir(string $dir): void
{
    if (! is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? rrmdir($path) : unlink($path);
    }

    rmdir($dir);
}

function confirm(string $question, bool $default = false): bool
{
    $answer = readline($question . ($default ? ' (Y/n): ' : ' (y/N): '));
    if (! $answer) {
        return $default;
    }

    return strtolower($answer) === 'y';
}

function export_php_array(array $array): string
{
    return "<?php\n\nreturn " . pretty_print_array($array) . ";\n";
}

function pretty_print_array(array $array, int $indent = 0): string
{
    $result = "[\n";
    $pad = str_repeat('    ', $indent + 1);

    foreach ($array as $key => $value) {
        $result .= $pad . var_export($key, true) . ' => ';
        if (is_array($value)) {
            $result .= pretty_print_array($value, $indent + 1);
        } else {
            $result .= var_export($value, true);
        }
        $result .= ",\n";
    }

    $result .= str_repeat('    ', $indent) . ']';

    return $result;
}
