<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use ZephyrIt\Shared\Filament\Resources\StateResource\Pages;
use ZephyrIt\Shared\Models\Country;
use ZephyrIt\Shared\Models\State;

class StateResource extends Resource
{
    protected static ?string $model = State::class;

    protected static ?string $navigationIcon = 'ri-government-line';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make(__('shared::labels.fieldset.general_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('shared::labels.name'))
                            ->required(),
                        Forms\Components\Select::make('country_id')
                            ->label(__('shared::labels.country'))
                            ->relationship('country', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('country_code')
                            ->label(__('shared::labels.country_code'))
                            ->required()
                            ->maxLength(2),
                    ])->columns(2),
                Forms\Components\Fieldset::make(__('shared::labels.fieldset.additional_information'))
                    ->schema([
                        Forms\Components\TextInput::make('fips_code')
                            ->label(__('shared::labels.fips_code'))
                            ->default(null),
                        Forms\Components\TextInput::make('iso2')
                            ->label(__('shared::labels.iso2'))
                            ->default(null),
                        Forms\Components\TextInput::make('type')
                            ->label(__('shared::labels.type'))
                            ->maxLength(191)
                            ->default(null),
                    ])->columns(2),
                Forms\Components\Fieldset::make(__('shared::labels.fieldset.geographical_information'))
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
                Forms\Components\Fieldset::make(__('shared::labels.fieldset.additional_details'))
                    ->schema([
                        Forms\Components\Toggle::make('flag')
                            ->label(__('shared::labels.flag'))
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
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('shared::labels.country'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('country_code')
                    ->label(__('shared::labels.country_code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('fips_code')
                    ->label(__('shared::labels.fips_code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('iso2')
                    ->label(__('shared::labels.iso2'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('shared::labels.type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->label(__('shared::labels.latitude'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->label(__('shared::labels.longitude'))
                    ->numeric()
                    ->sortable(),
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label(__('shared::labels.created_at')),
                Tables\Filters\SelectFilter::make('country_id')
                    ->label(__('shared::labels.country'))
                    ->options(fn (): array => Country::limit(50)->pluck('name', 'id')->toArray())
                    ->getSearchResultsUsing(fn (string $search): array => Country::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id')->toArray()),
                Tables\Filters\TernaryFilter::make('flag')
                    ->label(__('shared::labels.flag')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStates::route('/'),
        ];
    }
}
