<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use ZephyrIt\Shared\Filament\Resources\CityResource\Pages\ManageCities;
use ZephyrIt\Shared\Models\City;
use ZephyrIt\Shared\Models\Country;
use ZephyrIt\Shared\Models\State;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static string | BackedEnum | null $navigationIcon = 'ri-building-line';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('shared::navigations.labels.cities');
    }

    public static function getModelLabel(): string
    {
        return __('shared::modals.labels.city');
    }

    public static function getNavigationGroup(): string
    {
        return __('shared::navigations.groups.masters');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make(__('shared::labels.fieldset.general_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('shared::labels.name'))
                            ->required(),
                        Select::make('state_id')
                            ->label(__('shared::labels.state'))
                            ->relationship('state', 'name')
                            ->required(),
                        TextInput::make('state_code')
                            ->label(__('shared::labels.state_code'))
                            ->required(),
                        Select::make('country_id')
                            ->label(__('shared::labels.country'))
                            ->relationship('country', 'name')
                            ->required(),
                        TextInput::make('country_code')
                            ->label(__('shared::labels.country_code'))
                            ->required()
                            ->maxLength(2),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.geographical_information'))
                    ->schema([
                        TextInput::make('latitude')
                            ->label(__('shared::labels.latitude'))
                            ->required()
                            ->numeric(),
                        TextInput::make('longitude')
                            ->label(__('shared::labels.longitude'))
                            ->required()
                            ->numeric(),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.additional_details'))
                    ->schema([
                        ToggleButtons::make('flag')
                            ->label(__('shared::labels.flag'))
                            ->boolean()
                            ->grouped()
                            ->default(true)
                            ->required(),
                        TextInput::make('wikiDataId')
                            ->label(__('shared::labels.wikiDataId'))
                            ->default(null),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('shared::labels.name'))
                    ->searchable(),
                TextColumn::make('state.name')
                    ->label(__('shared::labels.state'))
                    ->sortable(),
                TextColumn::make('state_code')
                    ->label(__('shared::labels.state_code'))
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label(__('shared::labels.country'))
                    ->sortable(),
                TextColumn::make('country_code')
                    ->label(__('shared::labels.country_code'))
                    ->searchable(),
                TextColumn::make('latitude')
                    ->label(__('shared::labels.latitude'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->label(__('shared::labels.longitude'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('flag')
                    ->label(__('shared::labels.flag'))
                    ->boolean(),
                TextColumn::make('wikiDataId')
                    ->label(__('shared::labels.wikiDataId'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('shared::labels.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('shared::labels.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label(__('shared::labels.created_at')),
                SelectFilter::make('country_id')
                    ->label(__('shared::labels.country'))
                    ->options(fn (): array => Country::limit(50)->pluck('name', 'id')->toArray())
                    ->getSearchResultsUsing(fn (string $search): array => Country::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id')->toArray()),
                SelectFilter::make('state_id')
                    ->label(__('shared::labels.state'))
                    ->options(fn (): array => State::limit(50)->pluck('name', 'id')->toArray())
                    ->getSearchResultsUsing(fn (string $search): array => State::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id')->toArray()),
                TernaryFilter::make('flag')
                    ->label(__('shared::labels.flag')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCities::route('/'),
        ];
    }
}
