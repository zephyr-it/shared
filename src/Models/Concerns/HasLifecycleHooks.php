<?php

namespace ZephyrIt\Shared\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

trait HasLifecycleHooks
{
    /**
     * Flag to control whether related soft-deletable models
     * should also be deleted/restored in sync with this model.
     */
    protected bool $shouldSyncRelatedSoftDeletes = false;

    /**
     * Boot model and register lifecycle event hooks.
     * Hooks are wrapped in DB transactions unless already inside one.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Standard Eloquent lifecycle hooks
        static::registerTransactionalHook('creating', 'performCreating');
        static::registerTransactionalHook('created', 'performAfterCreate');
        static::registerTransactionalHook('updating', 'performUpdating');
        static::registerTransactionalHook('updated', 'performAfterUpdate');
        static::registerTransactionalHook('saving', 'handleSaving'); // custom soft delete logic
        static::registerTransactionalHook('saved', 'performAfterSave');
        static::registerTransactionalHook('deleting', 'handleDeleting'); // custom soft delete logic
        static::registerTransactionalHook('deleted', 'performAfterDelete');

        // Register soft delete hooks only if the model uses SoftDeletes
        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::registerTransactionalHook('restoring', 'performRestoring');
            static::registerTransactionalHook('restored', 'performAfterRestore');
        }
    }

    /**
     * Register a model event hook with automatic transaction guard.
     * Skips wrapping in DB transaction if already inside one.
     */
    protected static function registerTransactionalHook(string $event, string $method): void
    {
        static::{$event}(function ($model) use ($method) {
            $callback = fn () => method_exists($model, $method) ? $model->{$method}() : null;

            // Wrap only if not already in a transaction
            if (DB::transactionLevel() === 0) {
                DB::transaction($callback);
            } else {
                $callback();
            }
        });
    }

    /**
     * Before `saving` logic (used for create/update).
     * Handles restoration of related models if this model is being undeleted.
     */
    protected function handleSaving(): void
    {
        if (
            $this->shouldSyncRelatedSoftDeletes &&
            $this->isDirty('deleted_at') &&
            $this->deleted_at === null &&
            method_exists($this, 'relatedModelsForSoftDelete')
        ) {
            $this->restoreRelatedModels();
        }

        $this->performSaving();
    }

    /**
     * Before `deleting` logic.
     * Deletes related models if flag is enabled and relation methods are defined.
     */
    protected function handleDeleting(): void
    {
        if (
            $this->shouldSyncRelatedSoftDeletes &&
            method_exists($this, 'relatedModelsForSoftDelete')
        ) {
            $this->deleteRelatedModels();
        }

        $this->performDeleting();
    }

    /**
     * Stub methods — meant to be overridden in child models.
     */
    protected function performCreating(): void {}

    protected function performAfterCreate(): void {}

    protected function performUpdating(): void {}

    protected function performAfterUpdate(): void {}

    protected function performSaving(): void {}

    protected function performAfterSave(): void {}

    protected function performDeleting(): void {}

    protected function performAfterDelete(): void {}

    protected function performRestoring(): void {}

    protected function performAfterRestore(): void {}

    /**
     * Delete related models (typically called from `deleting` hook).
     */
    public function deleteRelatedModels(): void
    {
        foreach ($this->relatedModelsForSoftDelete() as $relation) {
            if (! method_exists($this, $relation)) {
                continue;
            }

            $relationInstance = $this->$relation();

            if (! ($relationInstance instanceof HasMany ||
                   $relationInstance instanceof HasOne ||
                   $relationInstance instanceof HasManyThrough ||
                   $relationInstance instanceof MorphMany)) {
                continue;
            }

            foreach ($relationInstance->get() as $relatedModel) {
                if (in_array(SoftDeletes::class, class_uses_recursive($relatedModel))) {
                    $relatedModel->delete();
                }
            }
        }
    }

    /**
     * Restore related models (typically called from `restoring` or `saving` hook).
     */
    public function restoreRelatedModels(): void
    {
        foreach ($this->relatedModelsForSoftDelete() as $relation) {
            if (! method_exists($this, $relation)) {
                continue;
            }

            $relatedModels = $this->$relation()->withTrashed()->get();

            foreach ($relatedModels as $relatedModel) {
                if (in_array(SoftDeletes::class, class_uses_recursive($relatedModel))) {
                    $relatedModel->restore();
                }
            }
        }
    }
}
