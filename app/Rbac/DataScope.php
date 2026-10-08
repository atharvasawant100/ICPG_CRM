<?php

namespace App\Rbac;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Resolves and applies the "own / team / all" data scope.
 *
 * Reads the module => owner column map and the role => scope map from
 * config/rbac.php so that neither is scattered through controllers or views.
 */
final class DataScope
{
    public const OWN = 'own';

    public const TEAM = 'team';

    public const ALL = 'all';

    /**
     * The owner column for a module, or null when the module is unscoped.
     */
    public static function ownerColumn(string $module): ?string
    {
        return self::ownerConfig($module)['column'] ?? null;
    }

    /**
     * The table a module's records live in. Needed by tests to assert the
     * configured column actually exists in the schema.
     */
    public static function ownerTable(string $module): ?string
    {
        return self::ownerConfig($module)['table'] ?? null;
    }

    /**
     * @return array{table: string, column: string}|null
     */
    private static function ownerConfig(string $module): ?array
    {
        $config = config("rbac.owner_columns.{$module}");

        if (! is_array($config) || ! isset($config['column'], $config['table'])) {
            return null;
        }

        return ['table' => $config['table'], 'column' => $config['column']];
    }

    /**
     * The scope a role has for a module.
     *
     * Resolution order:
     *   1. role_module_scopes (the DB, managed from the Roles screen)
     *   2. config/rbac.php    (fallback, keeps working if the table is empty)
     *   3. null               (caller falls back to default_scope)
     */
    public static function scopeForRole(string $role, string $module): ?string
    {
        try {
            $stored = DB::table('role_module_scopes')
                ->where('role_name', $role)
                ->where('module', $module)
                ->value('scope');

            if (is_string($stored) && in_array($stored, [self::OWN, self::TEAM, self::ALL], true)) {
                return $stored;
            }
        } catch (\Throwable) {
            // Table missing or unreachable: fall through to config.
        }

        $map = config("rbac.scopes.{$role}");

        if (is_array($map)) {
            $scope = $map[$module] ?? $map['default'] ?? null;

            if (is_string($scope) && in_array($scope, [self::OWN, self::TEAM, self::ALL], true)) {
                return $scope;
            }
        }

        return null;
    }

    /**
     * The scope a user has for a module.
     */
    public static function scopeFor(?User $user, string $module): string
    {
        if ($user === null) {
            // No user: the auth middleware will have rejected them already.
            // Deny by scoping to nothing rather than returning everything.
            return self::OWN;
        }

        // Ranked so the strongest scope wins if a user holds several roles.
        $rank = [self::OWN => 1, self::TEAM => 2, self::ALL => 3];

        $resolved = config('rbac.default_scope', self::OWN);
        $best = $rank[$resolved] ?? 1;

        foreach ($user->getRoleNames() as $role) {
            // DB first, config as fallback (see scopeForRole()).
            $scope = self::scopeForRole($role, $module);

            if (! is_string($scope) || ! isset($rank[$scope])) {
                continue;
            }

            if ($rank[$scope] > $best) {
                $best = $rank[$scope];
                $resolved = $scope;
            }

            if ($resolved === self::ALL) {
                break;
            }
        }

        return $resolved;
    }

    /**
     * The user ids visible under "team": the user plus their direct reports.
     *
     * @return list<int>
     */
    public static function teamIds(User $user): array
    {
        return User::query()
            ->where('manager_id', $user->id)
            ->pluck('id')
            ->push($user->id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Apply the scope for a user to a query.
     *
     * Returns the query unchanged for `all` and for modules with no owner
     * column, which is what keeps the Admin's results byte-identical to today.
     */
    public static function apply(Builder $query, ?User $user, string $module): Builder
    {
        $column = self::ownerColumn($module);

        if ($column === null) {
            return $query;
        }

        $scope = self::scopeFor($user, $module);

        if ($scope === self::ALL) {
            return $query;
        }

        // Qualify the column so joins cannot make it ambiguous.
        $qualified = $query->getModel()->qualifyColumn($column);

        if ($scope === self::TEAM && $user !== null) {
            return $query->whereIn($qualified, self::teamIds($user));
        }

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($qualified, $user->id);
    }

    /**
     * Whether a concrete record is visible to a user.
     *
     * Used by policies so that a user cannot open another person's record by
     * guessing an id.
     */
    public static function allowsRecord(?User $user, Model $record, string $module): bool
    {
        $column = self::ownerColumn($module);

        if ($column === null) {
            return true;
        }

        $scope = self::scopeFor($user, $module);

        if ($scope === self::ALL) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        $ownerId = $record->getAttribute($column);

        if ($ownerId === null) {
            // Unowned legacy rows: visible to nobody below "all" scope, so a
            // record nobody can prove ownership of is not leaked.
            return false;
        }

        if ((int) $ownerId === (int) $user->id) {
            return true;
        }

        if ($scope === self::TEAM) {
            return in_array((int) $ownerId, array_map('intval', self::teamIds($user)), true);
        }

        return false;
    }
}