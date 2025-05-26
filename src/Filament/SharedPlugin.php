<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Filament;

use Filament\Navigation\NavigationGroup;
use ZephyrIt\FilamentCustomizer\Base\Plugins\BasePlugin;

class SharedPlugin extends BasePlugin
{
    public function getId(): string
    {
        return 'shared';
    }

    protected function navigationGroups(): array
    {
        return [
            NavigationGroup::make()
                ->label(fn (): string => __('shared::navigations.groups.masters'))
                ->icon('tabler-database')
                ->collapsed(),
        ];
    }
}
