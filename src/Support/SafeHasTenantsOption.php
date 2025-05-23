<?php

namespace ZephyrIt\Shared\Support;

if (class_exists(\Stancl\Tenancy\Concerns\HasATenantsOption::class)) {
    trait SafeHasTenantsOption
    {
        use \Stancl\Tenancy\Concerns\HasATenantsOption;
    }
} else {
    trait SafeHasTenantsOption
    {
        // No-op fallback
    }
}
