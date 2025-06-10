<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Country
    |--------------------------------------------------------------------------
    |
    | The default country used across the application when no specific
    | country is provided. This is typically used in geolocation,
    | registration forms, or reporting defaults.
    |
    */
    'default_country' => env('DEFAULT_COUNTRY', 'India'),

    /*
    |--------------------------------------------------------------------------
    | User Model Resolver
    |--------------------------------------------------------------------------
    |
    | Dynamically determines which User model class should be used
    | based on the application context. It returns either the tenant
    | or central model as defined below in the `user_models` config.
    |
    | Usage:
    |   config('shared.user_model_resolver')()
    |
    */
    'user_model_resolver' => function () {
        if (function_exists('tenant') && tenant()) {
            return config('shared.user_models.tenant');
        }

        return config('shared.user_models.central');
    },

    /*
    |--------------------------------------------------------------------------
    | User Models
    |--------------------------------------------------------------------------
    |
    | Explicit class mappings for user models used in tenant and central
    | applications. These can be used when you want to access one
    | directly without resolving based on context.
    |
    */
    'user_models' => [
        'tenant' => App\Models\User::class,
        'central' => App\Models\User::class,
    ],
];
