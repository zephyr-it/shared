<?php

namespace ZephyrIt\Shared\Support;

use Stancl\Tenancy\Concerns\HasATenantsOption;

if (function_exists('tenant')) {
    trait SafeHasTenantsOption
    {
        use HasATenantsOption;
    }
} else {
    trait SafeHasTenantsOption
    {
        // No-op fallback
    }
}
