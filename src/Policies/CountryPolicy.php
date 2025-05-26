<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Policies;

class CountryPolicy extends BasePolicy
{
    protected string $resource = 'country';

    // protected array $permissions = [
    //     'reorder' => 'custom_permission_key',
    // ];

    // protected array $abilities = [
    //     'viewAny', 'view', 'create', 'update', 'delete',
    //     'deleteAny', 'forceDelete', 'forceDeleteAny',
    //     'restore', 'restoreAny', 'replicate', 'reorder',
    // ];
}
