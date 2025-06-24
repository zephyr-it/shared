<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Filament;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use ReflectionClass;

class SharedPlugin implements Plugin
{
    public function getId(): string
    {
        return 'shared-plugin';
    }

    public function register(Panel $panel): void
    {
        $panel->navigationGroups(self::getNavigationGroups());

        $resourcesPath = $this->resolvePath('Resources');
        if (is_dir($resourcesPath)) {
            $panel->discoverResources(
                in: $resourcesPath,
                for: $this->resolveNamespace('Resources'),
            );
        }

        $pagesPath = $this->resolvePath('Pages');
        if (is_dir($pagesPath)) {
            $panel->discoverPages(
                in: $pagesPath,
                for: $this->resolveNamespace('Pages'),
            );
        }

        $widgetsPath = $this->resolvePath('Widgets');
        if (is_dir($widgetsPath)) {
            $panel->discoverWidgets(
                in: $widgetsPath,
                for: $this->resolveNamespace('Widgets'),
            );
        }
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(static::make()->getId());
    }

    public static function getNavigationGroups(): array
    {
        return [
            NavigationGroup::make()
                ->label(fn () => __('shared::navigations.groups.masters'))
                ->icon('tabler-database')
                ->collapsed(),
        ];
    }

    protected function resolvePath(string $subDir): string
    {
        return dirname((new ReflectionClass(static::class))->getFileName()) . DIRECTORY_SEPARATOR . $subDir;
    }

    protected function resolveNamespace(string $subNamespace): string
    {
        $baseNamespace = (new ReflectionClass(static::class))->getNamespaceName();

        return $baseNamespace . '\\' . $subNamespace;
    }
}
