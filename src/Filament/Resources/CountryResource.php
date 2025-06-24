<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
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
use ZephyrIt\Shared\Filament\Resources\CountryResource\Pages\ManageCountries;
use ZephyrIt\Shared\Models\Country;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static string | BackedEnum | null $navigationIcon = 'ri-earth-line';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('shared::navigations.labels.countries');
    }

    public static function getModelLabel(): string
    {
        return __('shared::modals.labels.country');
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
                            ->required()
                            ->maxLength(100),
                        TextInput::make('iso3')
                            ->label(__('shared::labels.iso3'))
                            ->maxLength(3)
                            ->default(null),
                        TextInput::make('numeric_code')
                            ->label(__('shared::labels.numeric_code'))
                            ->maxLength(3)
                            ->default(null),
                        TextInput::make('iso2')
                            ->label(__('shared::labels.iso2'))
                            ->maxLength(2)
                            ->default(null),
                        TextInput::make('phonecode')
                            ->label(__('shared::labels.phonecode'))
                            ->tel()
                            ->default(null),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.additional_information'))
                    ->schema([
                        TextInput::make('capital')
                            ->label(__('shared::labels.capital'))
                            ->default(null),
                        TextInput::make('currency')
                            ->label(__('shared::labels.currency.title'))
                            ->default(null),
                        TextInput::make('currency_name')
                            ->label(__('shared::labels.currency.name'))
                            ->default(null),
                        TextInput::make('currency_symbol')
                            ->label(__('shared::labels.currency.symbol'))
                            ->default(null),
                        TextInput::make('tld')
                            ->label(__('shared::labels.tld'))
                            ->default(null),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.location_information'))
                    ->schema([
                        TextInput::make('native')
                            ->label(__('shared::labels.native'))
                            ->default(null),
                        TextInput::make('region')
                            ->label(__('shared::labels.region'))
                            ->default(null),
                        TextInput::make('region_id')
                            ->label(__('shared::labels.region_id'))
                            ->numeric()
                            ->default(null),
                        TextInput::make('subregion')
                            ->label(__('shared::labels.subregion'))
                            ->default(null),
                        TextInput::make('subregion_id')
                            ->label(__('shared::labels.subregion_id'))
                            ->numeric()
                            ->default(null),
                        TextInput::make('nationality')
                            ->label(__('shared::labels.nationality'))
                            ->default(null),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.timezone_information'))
                    ->schema([
                        Textarea::make('timezones')
                            ->label(__('shared::labels.timezones'))
                            ->columnSpanFull(),
                    ])->columns(1),
                Fieldset::make(__('shared::labels.fieldset.translation_information'))
                    ->schema([
                        Textarea::make('translations')
                            ->label(__('shared::labels.translations'))
                            ->columnSpanFull(),
                    ])->columns(1),
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
                        TextInput::make('emoji')
                            ->label(__('shared::labels.emoji'))
                            ->maxLength(191)
                            ->default(null),
                        TextInput::make('emojiU')
                            ->label(__('shared::labels.emojiU'))
                            ->maxLength(191)
                            ->default(null),
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
                TextColumn::make('iso3')
                    ->label(__('shared::labels.iso3'))
                    ->searchable(),
                TextColumn::make('numeric_code')
                    ->label(__('shared::labels.numeric_code'))
                    ->searchable(),
                TextColumn::make('iso2')
                    ->label(__('shared::labels.iso2'))
                    ->searchable(),
                TextColumn::make('phonecode')
                    ->label(__('shared::labels.phonecode'))
                    ->searchable(),
                TextColumn::make('capital')
                    ->label(__('shared::labels.capital'))
                    ->searchable(),
                TextColumn::make('currency')
                    ->label(__('shared::labels.currency.title'))
                    ->searchable(),
                TextColumn::make('currency_name')
                    ->label(__('shared::labels.currency.name'))
                    ->searchable(),
                TextColumn::make('currency_symbol')
                    ->label(__('shared::labels.currency.symbol'))
                    ->searchable(),
                TextColumn::make('tld')
                    ->label(__('shared::labels.tld'))
                    ->searchable(),
                TextColumn::make('native')
                    ->label(__('shared::labels.native'))
                    ->searchable(),
                TextColumn::make('region')
                    ->label(__('shared::labels.region'))
                    ->searchable(),
                TextColumn::make('region_id')
                    ->label(__('shared::labels.region_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('subregion')
                    ->label(__('shared::labels.subregion'))
                    ->searchable(),
                TextColumn::make('subregion_id')
                    ->label(__('shared::labels.subregion_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nationality')
                    ->label(__('shared::labels.nationality'))
                    ->searchable(),
                TextColumn::make('latitude')
                    ->label(__('shared::labels.latitude'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->label(__('shared::labels.longitude'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('emoji')
                    ->label(__('shared::labels.emoji'))
                    ->searchable(),
                TextColumn::make('emojiU')
                    ->label(__('shared::labels.emojiU'))
                    ->searchable(),
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
                SelectFilter::make('region')
                    ->label(__('shared::labels.region'))
                    ->options(Country::query()->pluck('region', 'region')->toArray()),
                SelectFilter::make('subregion')
                    ->label(__('shared::labels.subregion'))
                    ->options(Country::query()->pluck('subregion', 'subregion')->toArray()),
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
            'index' => ManageCountries::route('/'),
        ];
    }
}
