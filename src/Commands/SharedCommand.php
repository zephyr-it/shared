<?php

namespace ZephyrIt\Shared\Commands;

use Illuminate\Console\Command;

class SharedCommand extends Command
{
    protected $signature = 'shared:install {--force : Overwrite existing files without confirmation}';

    protected $description = 'Install Zephyr-IT Shared package with all configs, assets, scripts, and migrations.';

    public function handle(): int
    {
        $this->info('🔧 Installing Zephyr-IT Shared package...');

        // Publish shared package's own assets/config/migrations
        install_publish($this, [
            ['tag' => 'shared-config'],
            ['tag' => 'shared-assets'],
            ['tag' => 'shared-migrations'],
        ], $this->option('force'));

        // Publish external (vendor) dependencies like activitylog
        install_publish($this, [
            ['command' => 'activitylog-migrations'],
        ], $this->option('force'));

        $this->newLine();

        if ($this->confirm('💡 Would you like to run migrations now?', true)) {
            $this->call('migrate');
        }

        $this->info('✅ Zephyr-IT Shared installation complete.');

        return self::SUCCESS;
    }
}
