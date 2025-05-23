<?php

namespace ZephyrIt\Shared\Support;

/*
 * Trait SafeCentralConnection
 *
 * Uses Stancl\Tenancy's CentralConnection if available, else does nothing.
 */
if (class_exists(\Stancl\Tenancy\Database\Concerns\CentralConnection::class)) {
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
