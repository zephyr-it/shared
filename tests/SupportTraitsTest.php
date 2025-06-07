<?php

it('defines fallback traits when dependencies are missing', function () {
    expect(trait_exists(\ZephyrIt\Shared\Support\SafeCentralConnection::class))->toBeTrue();
    expect(trait_exists(\ZephyrIt\Shared\Support\SafeHasTenantsOption::class))->toBeTrue();
    expect(trait_exists(\ZephyrIt\Shared\Support\SafeTenantAwareCommand::class))->toBeTrue();
});
