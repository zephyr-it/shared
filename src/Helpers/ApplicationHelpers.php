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

        $tenant = self::safeTenant();

        if ($tenant && $tenant->country_id) {
            $country = Country::find($tenant->country_id);
        }

        if (! $country) {
            $defaultCountryName = config('shared.default_country', 'India');
            $country = Country::where('name', $defaultCountryName)->first();
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
        return function_exists('tenant') && tenancy()->initialized ? tenant() : null;
    }

    /**
     * Resolve accepted mime types for a given array of types.
     */
    public static function resolveAcceptedMimeTypes(array $types): array
    {
        $mimeGroups = [
            'image' => [
                'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                'ext' => ['.jpg', '.jpeg', '.png', '.webp', '.gif'],
            ],
            'svg' => [
                'mimes' => ['image/svg+xml'],
                'ext' => ['.svg'],
                'note' => 'Requires sanitization if user-uploaded',
            ],
            'image-other' => [
                'mimes' => ['image/bmp', 'image/tiff', 'image/x-icon', 'image/vnd.microsoft.icon'],
                'ext' => ['.bmp', '.tiff', '.ico'],
                'note' => 'Display may fail in browsers. Consider conversion.',
            ],
            'pdf' => [
                'mimes' => ['application/pdf'],
                'ext' => ['.pdf'],
            ],
            'zip' => [
                'mimes' => [
                    'application/zip',
                    'application/x-zip-compressed',
                    'application/octet-stream',
                    'application/x-rar-compressed',
                    'application/vnd.rar',
                    'application/x-rar',
                    'application/x-tar',
                ],
                'ext' => ['.zip', '.rar', '.tar'],
            ],
            'excel' => [
                'mimes' => [
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                    'application/vnd.ms-excel.sheet.macroEnabled.12',
                    'application/vnd.ms-excel.template.macroEnabled.12',
                ],
                'ext' => ['.xls', '.xlsx', '.xltx', '.xlsm', '.xltm'],
            ],
            'word' => [
                'mimes' => [
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
                ],
                'ext' => ['.doc', '.docx', '.dotx'],
            ],
            'text' => [
                'mimes' => ['text/plain'],
                'ext' => ['.txt'],
            ],
        ];

        $mimeFormats = [
            'png' => ['mimes' => ['image/png'], 'ext' => ['.png']],
            'jpg' => ['mimes' => ['image/jpeg'], 'ext' => ['.jpg', '.jpeg']],
            'jpeg' => ['mimes' => ['image/jpeg'], 'ext' => ['.jpeg']],
            'webp' => ['mimes' => ['image/webp'], 'ext' => ['.webp']],
            'gif' => ['mimes' => ['image/gif'], 'ext' => ['.gif']],
            'svg' => ['mimes' => ['image/svg+xml'], 'ext' => ['.svg']],
        ];

        $mimes = [];
        $extensions = [];
        $json = [];

        foreach ($types as $type) {
            $key = strtolower(trim($type));

            if (isset($mimeGroups[$key])) {
                $mimes = array_merge($mimes, $mimeGroups[$key]['mimes']);
                $extensions = array_merge($extensions, $mimeGroups[$key]['ext']);

                $json[] = [
                    'group' => $key,
                    'mimes' => $mimeGroups[$key]['mimes'],
                    'extensions' => $mimeGroups[$key]['ext'],
                    'note' => $mimeGroups[$key]['note'] ?? null,
                ];
            } elseif (isset($mimeFormats[$key])) {
                $mimes = array_merge($mimes, $mimeFormats[$key]['mimes']);
                $extensions = array_merge($extensions, $mimeFormats[$key]['ext']);

                $json[] = [
                    'group' => $key,
                    'mimes' => $mimeFormats[$key]['mimes'],
                    'extensions' => $mimeFormats[$key]['ext'],
                    'note' => null,
                ];
            } else {
                $mimes[] = $key;
                $json[] = [
                    'group' => 'custom',
                    'mimes' => [$key],
                    'extensions' => [],
                    'note' => 'Custom MIME type',
                ];
            }
        }

        return [
            'mimes' => array_unique($mimes),
            'helper' => count($extensions)
                ? implode(', ', array_unique($extensions))
                : implode(', ', array_unique($mimes)),
            'json' => $json,
        ];
    }
}
