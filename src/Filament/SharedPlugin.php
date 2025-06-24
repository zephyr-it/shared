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

        $panel->discoverResources(
            in: $this->resolvePath('Resources'),
            for: $this->resolveNamespace('Resources'),
        );

        $panel->discoverPages(
            in: $this->resolvePath('Pages'),
            for: $this->resolveNamespace('Pages'),
        );

        $panel->discoverWidgets(
            in: $this->resolvePath('Widgets'),
            for: $this->resolveNamespace('Widgets'),
        );
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
