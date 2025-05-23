<?php

namespace ZephyrIt\Shared\Support;

if (class_exists(\Stancl\Tenancy\Concerns\TenantAwareCommand::class)) {
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
