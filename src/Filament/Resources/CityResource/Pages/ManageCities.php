<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Filament\Resources\CityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use ZephyrIt\Shared\Filament\Resources\CityResource;

class ManageCities extends ManageRecords
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
