<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Traits;

use Illuminate\Http\Request;
use Stancl\Tenancy\Database\Models\Domain;

trait DeterminesTenant
{
    /**
     * Determine and return the tenant based on the request domain.
     *
     * @return mixed
     */
    protected function determineTenant()
    {
        /** @var Request $request */
        $request = request();
        $requestDomain = $request->getHost();
        $tenantDomain = app()->runningInConsole() ? null : Domain::where('domain', $requestDomain)->first();

        return $tenantDomain ? $tenantDomain->tenant : null;
    }
}
