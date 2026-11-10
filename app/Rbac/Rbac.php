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
     * The role a user gets when their designation is not one that elevates.
     *
     * Keyed by department name. A department with no entry falls back to
     * config/designations.php fallback_role, because departments are editable
     * and can be added without a matching role.
     */
    public const EMPLOYEE_ROLES = [
        'sales' => 'sales',
        'marketing' => 'marketing',
        'development' => 'development',
        'hr' => 'hr',
    ];

    /**
     * Department-specific manager roles.
     */
    public const DEPARTMENT_MANAGERS = [
        'sales' => 'sales_manager',
        'marketing' => 'marketing_manager',
        'development' => 'development_manager',
        'hr' => 'hr_manager',
    ];

    /**
     * The generic fallback role a department manager gets if no department match.
     */
    public const MANAGER_ROLE = 'manager';

    /**
     * Check if a given role name is a manager role.
     */
    public static function isManagerRole(?string $role): bool
    {
        if ($role === null || $role === '') {
            return false;
        }

        return $role === self::MANAGER_ROLE
            || in_array($role, self::DEPARTMENT_MANAGERS, true)
            || str_ends_with($role, '_manager');
    }

    /**
     * All seeded roles.
     */
    public const ROLES = [
        self::ADMIN_ROLE,
        'sales_manager',
        'marketing_manager',
        'development_manager',
        'hr_manager',
        'sales',
        'marketing',
        'development',
        'hr',
        'manager',
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
        // roles.edit guards saving the permission matrix for a role.
        'roles'              => ['view', 'edit'],
        'customers'          => ['view', 'create', 'edit', 'delete', 'export'],
        // import: bulk CSV/XLSX upload. convert: lead -> customer + invoice.
        // notes.*: notes are their own records with their own lifecycle.
        'leads'              => ['view', 'create', 'edit', 'delete', 'export', 'reassign', 'import', 'convert', 'notes.create', 'notes.delete'],
        'expenses'           => ['view', 'create', 'edit', 'delete', 'export', 'approve'],
        // Categories are a real module: own table, own controller, own page.
        'expense_categories' => ['view', 'create', 'edit', 'delete'],
        'services'           => ['view', 'create', 'edit', 'delete', 'export'],
        // send/cancel are irreversible external side effects. payments.* are
        // financial writes. All kept distinct from invoices.edit on purpose.
        // The single-invoice PDF download rides on invoices.export.
        'invoices'           => ['view', 'create', 'edit', 'delete', 'export', 'send', 'cancel', 'payments.create', 'payments.delete'],
        'recurring_invoices' => ['view', 'create', 'edit', 'delete', 'export', 'toggle'],
        'followups'          => ['view', 'create', 'edit', 'delete'],
        'team'               => ['view'],
        'my_attendance'      => ['view'],
        'my_leaves'          => ['view', 'create'],
        'my_expenses'        => ['view', 'create'],
        'users'              => ['view', 'create', 'edit', 'delete'],
        'payroll'            => ['view', 'create', 'edit', 'delete', 'export'],
        'attendance'         => ['view', 'create', 'edit', 'delete', 'export'],
        'leaves'             => ['view', 'create', 'edit', 'delete', 'approve', 'settings'],
        'holidays'           => ['view', 'create', 'edit', 'delete'],
        // Departments: list, add, and change the manager. No delete, because
        // users.department stores the department name as a string.
        'departments'        => ['view', 'create', 'edit'],
        'tasks'              => ['view', 'create', 'edit', 'delete'],
        'content'            => ['view', 'create', 'edit', 'delete', 'assign', 'review', 'publish', 'brands', 'accounts', 'reports'],
        'reports'            => ['costing', 'view', 'performance'],
        'settings'           => ['view'],
        'audit_logs'         => ['view'],
        // The email trail. Read-only like audit_logs: a row is written by the
        // queue, never edited by hand. No resend action here — re-sending is an
        // invoice action and stays behind invoices.send.
        'email_logs'         => ['view'],
        'sales_tracking'     => ['view'],
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