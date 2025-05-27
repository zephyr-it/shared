<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Country
    |--------------------------------------------------------------------------
    |
    | This value is used as the fallback/default country for the application,
    | typically used in forms or geolocation contexts when a country is
    | not explicitly defined.
    |
    */
    'default_country' => env('DEFAULT_COUNTRY', 'India'),

    /*
    |--------------------------------------------------------------------------
    | User Model Resolver
    |--------------------------------------------------------------------------
    |
    | Dynamically resolves the expected User model class based on tenancy.
    | This can return a class string or a closure that returns one.
    |
    | Example:
    | - For tenant context → Modules\URP\Models\User::class
    | - For central context → Modules\TURP\Models\User::class
    |
    */
    'user_model_resolver' => function () {
        return tenant()
            ? \App\Models\User::class
            : \App\Models\User::class;
    },
];
