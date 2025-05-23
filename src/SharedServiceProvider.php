<?php

namespace ZephyrIt\Shared;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ZephyrIt\Shared\Commands\SharedCommand;

class SharedServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('shared')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_shared_table')
            ->hasCommand(SharedCommand::class);
    }
}
