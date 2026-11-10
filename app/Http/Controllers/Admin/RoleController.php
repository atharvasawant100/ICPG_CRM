<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use App\Rbac\AuditLogger;
use App\Rbac\DataScope;
use App\Rbac\Rbac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    use AuthorizesByPermission;
    /**
     * List the seeded roles with user and permission counts.
     */
    public function index()
    {
        $this->authorizePermission('roles.view');

        $roleOrder = [
            'admin' => 1,
            'sales_manager' => 2,
            'marketing_manager' => 3,
            'development_manager' => 4,
            'hr_manager' => 5,
            'sales' => 6,
            'marketing' => 7,
            'development' => 8,
            'hr' => 9,
            'manager' => 10,
        ];

        $roles = Role::get()->sortBy(function (Role $role) use ($roleOrder) {
            return $roleOrder[$role->name] ?? 99;
        })->values()->map(function (Role $role) {
            return [
                'model' => $role,
                'users_count' => $role->users()->count(),
                'permissions_count' => $role->permissions()->count(),
            ];
        });

        return view('admin.roles.index', ['roles' => $roles]);
    }

    /**
     * Show the permission matrix for one role.
     */
    public function edit(Role $role)
    {
        $this->authorizePermission('roles.edit');

        return view('admin.roles.edit', [
            'role' => $role,
            'locked' => $role->name === Rbac::ADMIN_ROLE,
            'matrix' => $this->matrix(),
            'granted' => $role->permissions->pluck('name')->all(),
            'scopedModules' => array_keys(config('rbac.owner_columns', [])),
            'scopes' => $this->scopesFor($role->name),
        ]);
    }

    /**
     * Save a role's permissions and per-module data scopes.
     *
     * The admin role is refused here as well as in the UI, because hiding a
     * button is not enforcement.
     */
    public function update(Request $request, Role $role)
    {
        $this->authorizePermission('roles.edit');

        if ($role->name === Rbac::ADMIN_ROLE) {
            return back()->with('error', 'The admin role cannot be edited. Admin always has full access.');
        }

        $matrix = $this->matrix();
        $valid = [];

        // Read the nested array directly: actions such as "notes.create" contain
        // a dot, which dot-notation lookups would split into another level.
        $submitted = (array) $request->input('permissions', []);

        foreach ($matrix as $module => $actions) {
            $moduleInput = (array) ($submitted[$module] ?? []);

            foreach ($actions as $action => $permissionName) {
                if (filter_var($moduleInput[$action] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    $valid[] = $permissionName;
                }
            }
        }

        $before = $role->permissions->pluck('name')->all();

        // Only sync permissions that actually exist, so a hand-crafted request
        // cannot invent one.
        $valid = array_values(array_intersect($valid, Rbac::permissions()));

        // Spatie resolves names to ids from its cache. If the permissions table
        // was reseeded or imported, cached ids can point at rows that no longer
        // exist and the pivot insert fails its foreign key. Resolve fresh.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure every validated permission row exists in the database
        foreach ($valid as $permName) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => Rbac::GUARD,
            ]);
        }

        $role->syncPermissions($valid);

        $this->saveScopes($request, $role->name);

        // Drop the Spatie cache so the change takes effect immediately.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $after = $role->permissions()->pluck('name')->all();
        $this->auditPermissionChange($role, $before, $after);

        $added = array_values(array_diff($after, $before));
        $removed = array_values(array_diff($before, $after));

        $message = "Permissions updated for {$role->name}.";
        if ($added !== []) {
            $message .= ' Added: '.count($added).'.';
        }
        if ($removed !== []) {
            $message .= ' Removed: '.count($removed).'.';
        }

        return back()->with('success', $message);
    }

    /**
     * The module => action matrix as a map, so array_keys() yields action
     * names rather than positional integers.
     *
     * Generated from Rbac so the UI can never offer a cell the server does not
     * know about, and the server can never accept one it does not recognise.
     *
     * @return array<string, array<string, string>>
     */
    private function matrix(): array
    {
        $map = [];

        foreach (Rbac::MODULE_ACTIONS as $module => $actions) {
            foreach ($actions as $action) {
                $map[$module][$action] = "{$module}.{$action}";
            }
        }

        return $map;
    }

    /**
     * Current scopes for a role, keyed by module.
     *
     * @return array<string, string>
     */
    private function scopesFor(string $roleName): array
    {
        $scopes = [];

        foreach (array_keys(config('rbac.owner_columns', [])) as $module) {
            $scopes[$module] = DataScope::scopeForRole($roleName, $module)
                ?? DataScope::OWN;
        }

        return $scopes;
    }

    /**
     * Persist the per-module scope selections.
     */
    private function saveScopes(Request $request, string $roleName): void
    {
        $scoped = array_keys(config('rbac.owner_columns', []));

        foreach ($scoped as $module) {
            $value = $request->input("scopes.{$module}");

            if (! in_array($value, [DataScope::OWN, DataScope::TEAM, DataScope::ALL], true)) {
                $value = DataScope::OWN;
            }

            DB::table('role_module_scopes')->updateOrInsert(
                ['role_name' => $roleName, 'module' => $module],
                ['scope' => $value, 'created_at' => now(), 'updated_at' => now()],
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Record added and removed permissions in the audit trail.
     */
    private function auditPermissionChange(Role $role, array $before, array $after): void
    {
        $added = array_values(array_diff($after, $before));
        $removed = array_values(array_diff($before, $after));

        if ($added === [] && $removed === []) {
            return;
        }

        AuditLogger::record(
            'update',
            'roles',
            $role->id,
            ['permissions' => $before],
            ['permissions' => $after, 'added' => $added, 'removed' => $removed],
        );
    }
}
