<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use ZephyrIt\Shared\Filament\Resources\CountryResource\Pages;
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
                        Forms\Components\TextInput::make('name')
                            ->label(__('shared::labels.name'))
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('iso3')
                            ->label(__('shared::labels.iso3'))
                            ->maxLength(3)
                            ->default(null),
                        Forms\Components\TextInput::make('numeric_code')
                            ->label(__('shared::labels.numeric_code'))
                            ->maxLength(3)
                            ->default(null),
                        Forms\Components\TextInput::make('iso2')
                            ->label(__('shared::labels.iso2'))
                            ->maxLength(2)
                            ->default(null),
                        Forms\Components\TextInput::make('phonecode')
                            ->label(__('shared::labels.phonecode'))
                            ->tel()
                            ->default(null),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.additional_information'))
                    ->schema([
                        Forms\Components\TextInput::make('capital')
                            ->label(__('shared::labels.capital'))
                            ->default(null),
                        Forms\Components\TextInput::make('currency')
                            ->label(__('shared::labels.currency.title'))
                            ->default(null),
                        Forms\Components\TextInput::make('currency_name')
                            ->label(__('shared::labels.currency.name'))
                            ->default(null),
                        Forms\Components\TextInput::make('currency_symbol')
                            ->label(__('shared::labels.currency.symbol'))
                            ->default(null),
                        Forms\Components\TextInput::make('tld')
                            ->label(__('shared::labels.tld'))
                            ->default(null),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.location_information'))
                    ->schema([
                        Forms\Components\TextInput::make('native')
                            ->label(__('shared::labels.native'))
                            ->default(null),
                        Forms\Components\TextInput::make('region')
                            ->label(__('shared::labels.region'))
                            ->default(null),
                        Forms\Components\TextInput::make('region_id')
                            ->label(__('shared::labels.region_id'))
                            ->numeric()
                            ->default(null),
                        Forms\Components\TextInput::make('subregion')
                            ->label(__('shared::labels.subregion'))
                            ->default(null),
                        Forms\Components\TextInput::make('subregion_id')
                            ->label(__('shared::labels.subregion_id'))
                            ->numeric()
                            ->default(null),
                        Forms\Components\TextInput::make('nationality')
                            ->label(__('shared::labels.nationality'))
                            ->default(null),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.timezone_information'))
                    ->schema([
                        Forms\Components\Textarea::make('timezones')
                            ->label(__('shared::labels.timezones'))
                            ->columnSpanFull(),
                    ])->columns(1),
                Fieldset::make(__('shared::labels.fieldset.translation_information'))
                    ->schema([
                        Forms\Components\Textarea::make('translations')
                            ->label(__('shared::labels.translations'))
                            ->columnSpanFull(),
                    ])->columns(1),
                Fieldset::make(__('shared::labels.fieldset.geographical_information'))
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label(__('shared::labels.latitude'))
                            ->numeric()
                            ->default(null),
                        Forms\Components\TextInput::make('longitude')
                            ->label(__('shared::labels.longitude'))
                            ->numeric()
                            ->default(null),
                    ])->columns(2),
                Fieldset::make(__('shared::labels.fieldset.additional_details'))
                    ->schema([
                        Forms\Components\TextInput::make('emoji')
                            ->label(__('shared::labels.emoji'))
                            ->maxLength(191)
                            ->default(null),
                        Forms\Components\TextInput::make('emojiU')
                            ->label(__('shared::labels.emojiU'))
                            ->maxLength(191)
                            ->default(null),
                        Forms\Components\ToggleButtons::make('flag')
                            ->label(__('shared::labels.flag'))
                            ->boolean()
                            ->grouped()
                            ->default(true)
                            ->required(),
                        Forms\Components\TextInput::make('wikiDataId')
                            ->label(__('shared::labels.wikiDataId'))
                            ->default(null),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('shared::labels.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('iso3')
                    ->label(__('shared::labels.iso3'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('numeric_code')
                    ->label(__('shared::labels.numeric_code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('iso2')
                    ->label(__('shared::labels.iso2'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phonecode')
                    ->label(__('shared::labels.phonecode'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('capital')
                    ->label(__('shared::labels.capital'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label(__('shared::labels.currency.title'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency_name')
                    ->label(__('shared::labels.currency.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency_symbol')
                    ->label(__('shared::labels.currency.symbol'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('tld')
                    ->label(__('shared::labels.tld'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('native')
                    ->label(__('shared::labels.native'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('region')
                    ->label(__('shared::labels.region'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('region_id')
                    ->label(__('shared::labels.region_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subregion')
                    ->label(__('shared::labels.subregion'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('subregion_id')
                    ->label(__('shared::labels.subregion_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nationality')
                    ->label(__('shared::labels.nationality'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->label(__('shared::labels.latitude'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->label(__('shared::labels.longitude'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('emoji')
                    ->label(__('shared::labels.emoji'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('emojiU')
                    ->label(__('shared::labels.emojiU'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('flag')
                    ->label(__('shared::labels.flag'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('wikiDataId')
                    ->label(__('shared::labels.wikiDataId'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('shared::labels.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('shared::labels.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label(__('shared::labels.created_at')),
                Tables\Filters\SelectFilter::make('region')
                    ->label(__('shared::labels.region'))
                    ->options(Country::query()->pluck('region', 'region')->toArray()),
                Tables\Filters\SelectFilter::make('subregion')
                    ->label(__('shared::labels.subregion'))
                    ->options(Country::query()->pluck('subregion', 'subregion')->toArray()),
                Tables\Filters\TernaryFilter::make('flag')
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
            'index' => Pages\ManageCountries::route('/'),
        ];
    }
}
