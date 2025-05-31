<?php

namespace ZephyrIt\Shared\Commands;

use Illuminate\Console\Command;

class SharedInstallCommand extends Command
{
    protected $signature = 'shared:install 
                            {--force : Overwrite existing files without asking} 
                            {--tenant : Also publish tenant-specific migrations and settings} 
                            {--tenant-only : Only publish tenant-specific migrations and settings}';

    protected $description = 'Install ZephyrIt Shared package with optional tenant support';

    public function handle(): int
    {
        $this->info('🔧 Installing ZephyrIt Shared package...');

        $force = $this->option('force');
        $tenant = $this->option('tenant');
        $tenantOnly = $this->option('tenant-only');

        // ─── Core install (skipped if only tenant-specific) ───────────────────────
        if (! $tenantOnly) {
            install_publish($this, [
                ['tag' => 'shared-config'],
                ['tag' => 'shared-assets'],
                ['tag' => 'shared-migrations'],
            ], $force);

            install_publish_migrations(
                command: $this,
                sourceDir: __DIR__ . '/../../database/settings',
                type: 'settings'
            );
        }

        // Publish external (vendor) dependencies like activitylog
        install_publish($this, [
            ['command' => 'activitylog-migrations'],
        ], $force);

        // ─── Tenant install ───────────────────────────────────────────────────────
        if ($tenant || $tenantOnly) {
            install_publish_migrations(
                command: $this,
                sourceDir: __DIR__ . '/../../database/migrations',
                targetDir: database_path('tenant/migrations')
            );

            install_publish_migrations(
                command: $this,
                sourceDir: __DIR__ . '/../../database/settings',
                targetDir: database_path('tenant/settings')
            );

            $this->info('✅ Tenant-specific migrations and settings published.');
        }

        $this->newLine();

        if ($this->confirm('💡 Do you want to run migrations now?', true)) {
            $this->call('migrate');
        }

        $this->info('🎉 ZephyrIt Shared installed successfully.');

        return self::SUCCESS;
    }
}
