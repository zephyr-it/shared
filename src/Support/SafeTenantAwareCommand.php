<?php

namespace ZephyrIt\Shared\Support;

use Stancl\Tenancy\Concerns\TenantAwareCommand;

if (function_exists('tenant')) {
    trait SafeTenantAwareCommand
    {
        use TenantAwareCommand;
    }
} else {
    trait SafeTenantAwareCommand
    {
        // No-op fallback
    }
}
