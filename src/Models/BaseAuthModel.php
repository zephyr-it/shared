<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use ZephyrIt\Shared\Models\Concerns\HasCommonScopes;
use ZephyrIt\Shared\Models\Concerns\HasLifecycleHooks;

class BaseAuthModel extends Authenticatable
{
    use HasCommonScopes;
    use HasLifecycleHooks;
}
