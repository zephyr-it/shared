<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use ZephyrIt\Shared\Models\Concerns\HasCommonScopes;
use ZephyrIt\Shared\Models\Concerns\HasLifecycleHooks;

class BaseModel extends Model
{
    use HasCommonScopes;
    use HasLifecycleHooks;
    use LogsActivity;

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
