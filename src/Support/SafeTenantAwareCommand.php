<?php

namespace ZephyrIt\Shared\Support;

if (function_exists('tenant')) {
    trait SafeTenantAwareCommand
    {
        use \Stancl\Tenancy\Concerns\TenantAwareCommand;
    }
} else {
    trait SafeTenantAwareCommand
    {
        // No-op fallback
    }
}
