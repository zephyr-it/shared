<?php

namespace ZephyrIt\Shared;

use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SharedServiceProvider extends PackageServiceProvider
{
    public static string $name = 'shared';

    public function boot(): void
    {
        parent::boot();

        $this->scheduleCommands();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasCommands($this->getCommands());

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$name);
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }
    }

    /**
     * @return array<string>
     */
    private function getCommands(): array
    {
        return [
            Commands\SharedInstallCommand::class,
            Commands\Scheduling\Central\CleanupActivityLogCommand::class,
            Commands\Scheduling\Tenant\CleanupActivityLogCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_seeder_logs_table',
        ];
    }

    protected function scheduleCommands(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // 🌐 Central-only cleanup
            $schedule->command('shared:schedule:central:cleanup-activity-log')->dailyAt('00:10');

            // 🏢 Multi-tenant cleanup
            if (function_exists('tenant')) {
                $schedule->command('shared:schedule:tenant:cleanup-activity-log')->dailyAt('01:00');
            }
        });
    }
}
