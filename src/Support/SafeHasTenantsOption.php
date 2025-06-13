<?php

namespace ZephyrIt\Shared\Support;

if (function_exists('tenant')) {
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
