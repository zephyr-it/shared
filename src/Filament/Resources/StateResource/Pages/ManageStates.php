<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Filament\Resources\StateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use ZephyrIt\Shared\Filament\Resources\StateResource;

class ManageStates extends ManageRecords
{
    protected static string $resource = StateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
