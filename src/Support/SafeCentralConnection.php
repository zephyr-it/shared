<?php

namespace ZephyrIt\Shared\Support;

/*
 * Trait SafeCentralConnection
 *
 * Uses Stancl\Tenancy's CentralConnection if available, else does nothing.
 */

if (function_exists('tenant')) {
    trait SafeCentralConnection
    {
        use \Stancl\Tenancy\Database\Concerns\CentralConnection;
    }
} else {
    trait SafeCentralConnection
    {
        // No-op trait fallback
    }
}
