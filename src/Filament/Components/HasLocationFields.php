<?php

namespace ZephyrIt\Shared\Filament\Components;

use Filament\Forms;
use ZephyrIt\Shared\Models\City;
use ZephyrIt\Shared\Models\Country;
use ZephyrIt\Shared\Models\State;

trait HasLocationFields
{
    /**
     * User selects Country → State → City (manual flow).
     */
    protected static function locationCountryFirst(): array
    {
        return [
            Forms\Components\Select::make('country_id')
                ->label(__('shared::labels.country'))
                ->searchable()
                ->reactive()
                ->required()
                ->default(fn () => Country::where('name', 'India')->value('id'))
                ->getSearchResultsUsing(
                    fn (string $search) => Country::where('name', 'like', "%{$search}%")
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->getOptionLabelUsing(fn ($value) => Country::find($value)?->name)
                ->afterStateUpdated(fn (Forms\Set $set) => $set('state_id', null)->set('city_id', null)),

            Forms\Components\Select::make('state_id')
                ->label(__('shared::labels.state'))
                ->searchable()
                ->reactive()
                ->required()
                ->getSearchResultsUsing(
                    fn (string $search, Forms\Get $get) => State::where('country_id', $get('country_id'))
                        ->where('name', 'like', "%{$search}%")
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->getOptionLabelUsing(fn ($value) => State::find($value)?->name)
                ->afterStateUpdated(fn (Forms\Set $set) => $set('city_id', null)),

            Forms\Components\Select::make('city_id')
                ->label(__('shared::labels.city'))
                ->searchable()
                ->required()
                ->getSearchResultsUsing(
                    fn (string $search, Forms\Get $get) => City::where('state_id', $get('state_id'))
                        ->where('name', 'like', "%{$search}%")
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->getOptionLabelUsing(fn ($value) => City::find($value)?->name),
        ];
    }

    /**
     * User selects City → auto-selects State & Country (readonly).
     */
    protected static function locationCityFirst(): array
    {
        return [
            Forms\Components\Select::make('city_id')
                ->label(__('shared::labels.city'))
                ->searchable()
                ->reactive()
                ->required()
                ->getSearchResultsUsing(
                    fn (string $search) => City::with('state.country')
                        ->where('name', 'like', "%{$search}%")
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn ($city) => [
                            $city->id => "{$city->name}, {$city->state->name}, {$city->state->country->name}",
                        ])
                        ->toArray()
                )
                ->getOptionLabelUsing(function ($value) {
                    $city = City::with('state.country')->find($value);

                    return $city
                        ? "{$city->name}, {$city->state->name}, {$city->state->country->name}"
                        : null;
                })
                ->afterStateUpdated(function (?int $cityId, Forms\Set $set) {
                    $city = City::with('state.country')->find($cityId);
                    if ($city) {
                        $set('state_id', $city->state->id);
                        $set('country_id', $city->state->country->id);
                    } else {
                        $set('state_id', null);
                        $set('country_id', null);
                    }
                }),

            Forms\Components\Select::make('state_id')
                ->label(__('shared::labels.state'))
                ->searchable()
                ->disabled()
                ->getSearchResultsUsing(
                    fn (string $search) => State::where('name', 'like', "%{$search}%")
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->getOptionLabelUsing(fn ($value) => State::find($value)?->name),

            Forms\Components\Select::make('country_id')
                ->label(__('shared::labels.country'))
                ->searchable()
                ->disabled()
                ->getSearchResultsUsing(
                    fn (string $search) => Country::where('name', 'like', "%{$search}%")
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->getOptionLabelUsing(fn ($value) => Country::find($value)?->name),
        ];
    }
}
