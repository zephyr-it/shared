<?php

namespace ZephyrIt\Shared\Helpers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class InstallHelper
{
    /**
     * Run vendor publishes, tag-based, provider-based or command-based.
     */
    public static function publish(Command $command, array $items, bool $force = false): void
    {
        foreach ($items as $item) {
            if (isset($item['command'])) {
                $command->call($item['command']);

                continue;
            }

            $args = [];

            if (isset($item['tag'])) {
                $args['--tag'] = $item['tag'];
            }

            if (isset($item['provider'])) {
                $args['--provider'] = $item['provider'];
            }

            if ($force) {
                $args['--force'] = true;
            }

            $command->call('vendor:publish', $args);
        }
    }

    /**
     * Copy custom migration files (typically settings) to Laravel's settings directory.
     */
    public static function publishMigrations(
        Command $command,
        string $sourceDir,
        ?string $targetDir = null,
        string $type = 'settings',
        bool $timestamp = true
    ): void {
        $targetDir ??= database_path($type);

        if (! File::exists($sourceDir)) {
            $command->warn("⚠️  No source migrations found at: {$sourceDir}");

            return;
        }

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
            $command->info("📁 Created migration directory: {$targetDir}");
        }

        $files = glob($sourceDir . '/*.php');

        if (! $files) {
            $command->warn("⚠️  No migration files found in {$sourceDir}.");

            return;
        }

        foreach ($files as $path) {
            $filename = basename($path);
            $existingMatches = glob($targetDir . '/*_' . $filename);

            foreach ($existingMatches as $existingPath) {
                File::delete($existingPath);
                $command->info('♻️  Replaced existing: ' . basename($existingPath));
            }

            $prefix = $timestamp ? now()->format('Y_m_d_His') . '_' : '';
            $targetPath = "{$targetDir}/{$prefix}{$filename}";

            File::copy($path, $targetPath);
            $command->info("📦 Published: {$filename} → " . basename($targetPath));

            if ($timestamp) {
                sleep(1);
            }
        }
    }

    /**
     * Recursively copy publishable assets/configs from a `publish/` folder.
     */
    public static function publishFiles(
        Command $command,
        string $sourceDir,
        array $map = [
            'config' => 'config_path',
            'public' => 'public_path',
        ]
    ): void {
        if (! File::isDirectory($sourceDir)) {
            $command->warn("⚠️  No 'publish' directory found at: {$sourceDir}");

            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $segments = explode(DIRECTORY_SEPARATOR, $relativePath);
            $first = $segments[0] ?? null;

            if ($first && isset($map[$first])) {
                $target = call_user_func($map[$first]) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array_slice($segments, 1));
            } else {
                $target = base_path($relativePath);
            }

            File::ensureDirectoryExists(dirname($target));
            File::copy($file->getPathname(), $target);

            $command->info("📦 Published: {$relativePath} → {$target}");
        }
    }
}
