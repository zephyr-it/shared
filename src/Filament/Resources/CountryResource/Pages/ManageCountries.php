<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Filament\Resources\CountryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use ZephyrIt\Shared\Filament\Resources\CountryResource;

class ManageCountries extends ManageRecords
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
