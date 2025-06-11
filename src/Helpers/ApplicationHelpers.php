<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Helpers;

use ZephyrIt\Shared\Models\Country;

class ApplicationHelpers
{
    /**
     * Return an array of standard document details.
     */
    public static function documentDetails(): array
    {
        return [
            'Aadhaar Card',
            'PAN Card',
            'GSTIN',
            'Passport',
            'Voter ID',
            'Driving License',
            'Ration Card',
            'Birth Certificate',
            'Income Certificate',
            'Caste Certificate',
            'Domicile Certificate',
            'Marriage Certificate',
            'Property Documents',
            'EPF Account Number',
            'ESIC Card',
            'Pension Certificate',
            'Death Certificate',
            'NPR Number',
            'Health Insurance Card',
            'BPL Card',
        ];
    }

    /**
     * Get the currency symbol for the current tenant.
     */
    public static function getCurrencySymbol(): string
    {
        $country = null;

        if (self::safeTenant()?->country_id) {
            $country = Country::find(tenant()->country_id);
        }

        if (! $country) {
            $defaultCountry = config('shared.default_country', 'India');
            $country = Country::where('name', $defaultCountry)->first();
        }

        return $country?->currency_symbol ?? '';
    }

    /**
     * Return an array of denominations for a specific type.
     */
    public static function getDenominationsArray(string $type): array
    {
        return [
            ['value' => 500, 'count' => 0, 'amount' => 0, 'type' => $type],
            ['value' => 200, 'count' => 0, 'amount' => 0, 'type' => $type],
            ['value' => 100, 'count' => 0, 'amount' => 0, 'type' => $type],
            ['value' => 50, 'count' => 0, 'amount' => 0, 'type' => $type],
            ['value' => 20, 'count' => 0, 'amount' => 0, 'type' => $type],
            ['value' => 10, 'count' => 0, 'amount' => 0, 'type' => $type],
            ['value' => 5, 'count' => 0, 'amount' => 0, 'type' => $type],
            ['value' => 2, 'count' => 0, 'amount' => 0, 'type' => $type],
            ['value' => 1, 'count' => 0, 'amount' => 0, 'type' => $type],
        ];
    }

    /**
     * Transform supported locales into a simplified array.
     */
    public static function transformSupportedLocales(): array
    {
        return array_map(fn ($locale) => $locale['name'], config('laravellocalization.supportedLocales', []));
    }

    /**
     * Get the current tenant safely.
     */
    public static function safeTenant()
    {
        return function_exists('tenant') ? tenant() : null;
    }
}
