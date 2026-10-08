<?php

namespace App\Rbac;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Adds applyDataScope() to Eloquent models.
 *
 * Usage:
 *   Customer::query()->applyDataScope($user, 'customers')
 */
trait HasDataScope
{
    /**
     * Restrict the query to what the user is allowed to see.
     *
     * @param  \App\Models\User|null  $user
     */
    public function scopeApplyDataScope(Builder $query, ?User $user, string $module): Builder
    {
        return DataScope::apply($query, $user, $module);
    }

    /**
     * The scope the user has for this model's module.
     */
    public function dataScopeFor(?User $user, string $module): string
    {
        return DataScope::scopeFor($user, $module);
    }
}