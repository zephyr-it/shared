<?php

namespace ZephyrIt\Shared\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait HasCommonScopes
{
    /**
     * Safely check if the model's table has the given column.
     */
    protected function hasColumn(Builder $query, string $column): bool
    {
        return Schema::hasColumn($query->getModel()->getTable(), $column);
    }

    /**
     * Scope: where is_active = true.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $this->hasColumn($query, 'is_active')
            ? $query->where($query->getModel()->getTable() . '.is_active', true)
            : $query;
    }

    /**
     * Scope: where is_active = false.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $this->hasColumn($query, 'is_active')
            ? $query->where($query->getModel()->getTable() . '.is_active', false)
            : $query;
    }

    /**
     * Scope: general boolean column (safe).
     */
    public function scopeWhereBoolean(Builder $query, string $column, bool $value = true): Builder
    {
        return $this->hasColumn($query, $column)
            ? $query->where($query->getModel()->getTable() . '.' . $column, $value)
            : $query;
    }

    /**
     * Scope: where status = value.
     */
    public function scopeWhereStatus(Builder $query, string $status): Builder
    {
        return $this->hasColumn($query, 'status')
            ? $query->where($query->getModel()->getTable() . '.status', $status)
            : $query;
    }

    /**
     * Scope: dynamic ordering.
     */
    public function scopeOrdered(Builder $query, string $column = 'created_at', string $direction = 'desc'): Builder
    {
        return $query->orderBy($column, $direction);
    }

    /**
     * Scope: order by latest created_at.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    /**
     * Scope: order by any column, newest first.
     */
    public function scopeLatestFirst(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->orderByDesc($column);
    }

    /**
     * Scope: order by any column, oldest first.
     */
    public function scopeOldestFirst(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->orderBy($column);
    }

    /**
     * Scope: search column with LIKE %term%.
     */
    public function scopeSearch(Builder $query, string $column, string $term): Builder
    {
        return $this->hasColumn($query, $column)
            ? $query->where($query->getModel()->getTable() . '.' . $column, 'like', '%' . $term . '%')
            : $query;
    }
}
