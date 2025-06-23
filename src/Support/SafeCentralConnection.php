<?php

namespace ZephyrIt\Shared\Support;

use Stancl\Tenancy\Database\Concerns\CentralConnection;

/*
 * Trait SafeCentralConnection
 *
 * Uses Stancl\Tenancy's CentralConnection if available, else does nothing.
 */
if (function_exists('tenant')) {
    trait SafeCentralConnection
    {
        use CentralConnection;
    }
} else {
    trait SafeCentralConnection
    {
        // No-op trait fallback
    }
}
