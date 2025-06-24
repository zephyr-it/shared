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
use ZephyrIt\Shared\Filament\Resources\StateResource\Pages\ManageStates;
use ZephyrIt\Shared\Models\Country;
use ZephyrIt\Shared\Models\State;

class StateResource extends Resource
{
    protected static ?string $model = State::class;

    protected static string | BackedEnum | null $navigationIcon = 'ri-government-line';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('shared::navigations.labels.states');
    }

    public static function getModelLabel(): string
    {
        return __('shared::modals.labels.state');
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
                        Select::make('country_id')
                            ->label(__('shared::labels.country'))
                            ->relationship('country', 'name')
                            ->required(),
                        TextInput::make('country_code')
                            ->label(__('shared::labels.country_code'))
                            ->required()
                            ->maxLength(2),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.additional_information'))
                    ->schema([
                        TextInput::make('fips_code')
                            ->label(__('shared::labels.fips_code'))
                            ->default(null),
                        TextInput::make('iso2')
                            ->label(__('shared::labels.iso2'))
                            ->default(null),
                        TextInput::make('type')
                            ->label(__('shared::labels.type'))
                            ->maxLength(191)
                            ->default(null),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.geographical_information'))
                    ->schema([
                        TextInput::make('latitude')
                            ->label(__('shared::labels.latitude'))
                            ->numeric()
                            ->default(null),
                        TextInput::make('longitude')
                            ->label(__('shared::labels.longitude'))
                            ->numeric()
                            ->default(null),
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
                TextColumn::make('country.name')
                    ->label(__('shared::labels.country'))
                    ->sortable(),
                TextColumn::make('country_code')
                    ->label(__('shared::labels.country_code'))
                    ->searchable(),
                TextColumn::make('fips_code')
                    ->label(__('shared::labels.fips_code'))
                    ->searchable(),
                TextColumn::make('iso2')
                    ->label(__('shared::labels.iso2'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('shared::labels.type'))
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
            'index' => ManageStates::route('/'),
        ];
    }
}
