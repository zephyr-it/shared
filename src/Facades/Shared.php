<?php

namespace ZephyrIt\Shared\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ZephyrIt\Shared\Shared
 */
class Shared extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ZephyrIt\Shared\Shared::class;
    }
}
