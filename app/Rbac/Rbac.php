<?php

namespace App\Rbac;

/**
 * Single source of truth for the RBAC vocabulary.
 *
 * Roles and permissions are declared here rather than inline in the seeder so
 * that routes, controllers, policies, the sidebar and the tests all validate
 * against the same list.
 */
final class Rbac
{
    /**
     * The guard used for every role and permission in this application.
     */
    public const GUARD = 'web';

    /**
     * The role that bypasses all permission checks via Gate::before.
     */
    public const ADMIN_ROLE = 'admin';

    /**
     * All seeded roles. Only ADMIN_ROLE is wired up in this phase; the rest are
     * created empty so their permission sets can be defined in later phases.
     */
    public const ROLES = [
        self::ADMIN_ROLE,
        'manager',
        'sales',
        'marketing',
        'development',
        'hr',
    ];

    /**
     * Actions that apply to every module.
     */
    public const BASE_ACTIONS = ['view', 'create', 'edit', 'delete', 'export'];

    /**
     * Module => the actions that actually make sense for it.
     *
     * Deliberately omitted:
     *  - export on followups/users/roles/settings/audit_logs: not meaningful.
     *    You do not export a permission matrix or an audit trail.
     *  - create/edit/delete on roles/settings/audit_logs: read-only surfaces.
     *    settings is a placeholder, audit_logs is append-only, and role changes
     *    are made through users.edit + roles.view.
     */
    public const MODULE_ACTIONS = [
        'dashboard'          => ['view'],
        'customers'          => ['view', 'create', 'edit', 'delete', 'export'],
        'leads'              => ['view', 'create', 'edit', 'delete', 'export', 'reassign'],
        'expenses'           => ['view', 'create', 'edit', 'delete', 'export', 'approve'],
        'services'           => ['view', 'create', 'edit', 'delete', 'export'],
        'invoices'           => ['view', 'create', 'edit', 'delete', 'export'],
        'recurring_invoices' => ['view', 'create', 'edit', 'delete', 'export'],
        'followups'          => ['view', 'create', 'edit', 'delete'],
        'users'              => ['view', 'create', 'edit', 'delete'],
        'roles'              => ['view'],
        'settings'           => ['view'],
        'audit_logs'         => ['view'],
    ];

    /**
     * Every permission name, as "module.action".
     *
     * @return list<string>
     */
    public static function permissions(): array
    {
        $permissions = [];

        foreach (self::MODULE_ACTIONS as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        return $permissions;
    }

    /**
     * Legacy users.role values that map onto a seeded role.
     *
     * 'employee' is intentionally absent: the old enum allowed it, but mapping
     * it to a real role would silently grant that role's permissions to anyone
     * restored from an old backup. Unmapped values are reported instead.
     *
     * @return array<string, string|null>
     */
    public static function legacyRoleMap(): array
    {
        return [
            'admin' => self::ADMIN_ROLE,
        ];
    }
}