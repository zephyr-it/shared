<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\FilamentBase\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;
use ReflectionClass;

abstract class BasePlugin implements Plugin
{
    protected bool $registerResources = true;

    protected bool $registerPages = true;

    protected bool $registerWidgets = true;

    abstract public function getId(): string;

    protected function navigationGroups(): array
    {
        return [];
    }

    public function register(Panel $panel): void
    {
        $panel->navigationGroups($this->navigationGroups());

        if ($this->shouldRegisterPages()) {
            $panel->discoverPages(
                in: $this->path('Pages'),
                for: $this->namespace('Pages')
            );
        }

        if ($this->shouldRegisterResources()) {
            $panel->discoverResources(
                in: $this->path('Resources'),
                for: $this->namespace('Resources')
            );
        }

        if ($this->shouldRegisterWidgets()) {
            $panel->discoverWidgets(
                in: $this->path('Widgets'),
                for: $this->namespace('Widgets')
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
        return filament(app(static::class)->getId());
    }

    public function registerResources(bool $register = true): static
    {
        $this->registerResources = $register;

        return $this;
    }

    public function shouldRegisterResources(): bool
    {
        return $this->registerResources;
    }

    public function registerPages(bool $register = true): static
    {
        $this->registerPages = $register;

        return $this;
    }

    public function shouldRegisterPages(): bool
    {
        return $this->registerPages;
    }

    public function registerWidgets(bool $register = true): static
    {
        $this->registerWidgets = $register;

        return $this;
    }

    public function shouldRegisterWidgets(): bool
    {
        return $this->registerWidgets;
    }

    protected function path(string $subDir): string
    {
        $reflector = new ReflectionClass(static::class);
        $baseDir = dirname($reflector->getFileName());

        return $baseDir . DIRECTORY_SEPARATOR . $subDir;
    }

    protected function namespace(string $subNamespace): string
    {
        $reflector = new ReflectionClass(static::class);
        $baseNamespace = $reflector->getNamespaceName();

        if (str_ends_with($baseNamespace, '\\Plugins')) {
            $baseNamespace = substr($baseNamespace, 0, -strlen('\\Plugins'));
        }

        return $baseNamespace . '\\' . $subNamespace;
    }
}
