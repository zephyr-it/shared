<?php

namespace ZephyrIt\Shared\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use LogicException;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Default resource (inferred from class name unless overridden).
     */
    protected string $resource;

    /**
     * Override specific permission keys per ability.
     */
    protected array $permissions = [];

    /**
     * Explicit list of allowed abilities. Prevents accidental access.
     */
    protected array $abilities = [
        'viewAny', 'view', 'create', 'update', 'delete', 'deleteAny',
        'forceDelete', 'forceDeleteAny', 'restore', 'restoreAny',
        'replicate', 'reorder',
    ];

    public function __construct()
    {
        $this->resource = $this->resource ?? $this->resolveResourceName();
    }

    protected function resolveResourceName(): string
    {
        return strtolower(str_replace('policy', '', class_basename(static::class)));
    }

    protected function getExpectedUserModel(): string
    {
        $resolver = config('policy.user_model_resolver');

        return is_callable($resolver)
            ? call_user_func($resolver)
            : $resolver;
    }

    protected function hasPermission($user, string $permission): bool
    {
        $expectedClass = $this->getExpectedUserModel();

        if (! is_object($user) || ! is_a($user, $expectedClass)) {
            return false;
        }

        return method_exists($user, 'can') && $user->can($permission);
    }

    protected function resolvePermission(string $ability): string
    {
        return $this->permissions[$ability] ?? "{$ability}_{$this->resource}";
    }

    public function __call(string $method, array $args): bool
    {
        if (! in_array($method, $this->abilities)) {
            throw new LogicException("❌ Unsupported policy ability: {$method} in " . static::class);
        }

        $user = $args[0] ?? null;

        return $this->hasPermission($user, $this->resolvePermission($method));
    }
}
