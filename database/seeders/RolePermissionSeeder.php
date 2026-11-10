<?php

namespace Database\Seeders;

use App\Rbac\Rbac;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the RBAC vocabulary.
 *
 * Idempotent: safe to run repeatedly. Permissions and roles are created with
 * firstOrCreate and never deleted, so running this on production will not
 * disturb existing assignments.
 *
 * Note that the admin role is deliberately left with NO explicit permissions.
 * Admin access is granted by Gate::before in App\Providers\AppServiceProvider,
 * which means a permission added later can never lock the admin out.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * E-mails that must hold the admin role. Deliberately not user ids, so the
     * seeder is repeatable across environments.
     *
     * @var list<string>
     */
    public const ADMIN_EMAILS = ['admin@iconicpages.com'];

    /**
     * The departments that existed before this table did.
     *
     * users.department already holds these values, so they must exist as rows
     * for the existing users to keep matching a department.
     *
     * @var list<string>
     */
    public const DEFAULT_DEPARTMENTS = ['sales', 'marketing', 'development', 'hr'];

    public function run(): void
    {
        $this->seedPermissions();
        $this->seedRoles();
        $this->seedDepartments();
        $this->seedDataScopes();
        $this->assignAdminRoles();
        $this->flushCache();

        $this->command?->newLine();
        $this->command?->info('Roles and permissions seeded.');
        $this->reportAdmins();
    }

    /**
     * Create every "module.action" permission.
     */
    protected function seedPermissions(): void
    {
        foreach (Rbac::permissions() as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => Rbac::GUARD,
            ]);
        }

        $this->command?->info(sprintf(
            'Permissions: %d available for guard "%s".',
            count(Rbac::permissions()),
            Rbac::GUARD
        ));
    }

    /**
     * Create the roles. Only admin is populated, and only via Gate::before.
     */
    protected function seedRoles(): void
    {
        foreach (Rbac::ROLES as $name) {
            Role::firstOrCreate([
                'name' => $name,
                'guard_name' => Rbac::GUARD,
            ]);
        }

        // Grant employee self-service & dashboard permissions to all non-admin roles
        $selfServicePermissions = [
            'dashboard.view',
            'my_attendance.view',
            'my_leaves.view',
            'my_leaves.create',
            'my_expenses.view',
            'my_expenses.create',
        ];
        foreach (Rbac::ROLES as $roleName) {
            if ($roleName === Rbac::ADMIN_ROLE) {
                continue;
            }
            $role = Role::where('name', $roleName)->where('guard_name', Rbac::GUARD)->first();
            if ($role) {
                $role->givePermissionTo($selfServicePermissions);
            }
        }

        // Sales Manager
        $salesManagerRole = Role::where('name', 'sales_manager')->where('guard_name', Rbac::GUARD)->first();
        if ($salesManagerRole) {
            $salesManagerRole->syncPermissions(array_merge($selfServicePermissions, [
                'team.view',
                'reports.performance',
                'sales_tracking.view',
                'customers.view', 'customers.create', 'customers.edit', 'customers.delete', 'customers.export',
                'leads.view', 'leads.create', 'leads.edit', 'leads.delete', 'leads.export', 'leads.reassign', 'leads.import', 'leads.convert', 'leads.notes.create', 'leads.notes.delete',
                'followups.view', 'followups.create', 'followups.edit', 'followups.delete',
                'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.export',
                'expenses.view', 'expenses.approve',
                'attendance.view',
                'leaves.view', 'leaves.approve',
            ]));
        }

        // Marketing Manager - Strictly NO Sales module permissions
        $marketingManagerRole = Role::where('name', 'marketing_manager')->where('guard_name', Rbac::GUARD)->first();
        if ($marketingManagerRole) {
            $marketingManagerRole->syncPermissions(array_merge($selfServicePermissions, [
                'team.view',
                'reports.performance',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
                'content.view', 'content.create', 'content.edit', 'content.delete', 'content.assign', 'content.review', 'content.publish', 'content.brands', 'content.accounts', 'content.reports',
                'expenses.view', 'expenses.approve',
                'attendance.view',
                'leaves.view', 'leaves.approve',
            ]));
        }

        // Development Manager - Strictly NO Sales module permissions
        $devManagerRole = Role::where('name', 'development_manager')->where('guard_name', Rbac::GUARD)->first();
        if ($devManagerRole) {
            $devManagerRole->syncPermissions(array_merge($selfServicePermissions, [
                'team.view',
                'reports.performance',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
                'expenses.view', 'expenses.approve',
                'attendance.view',
                'leaves.view', 'leaves.approve',
            ]));
        }

        // HR Manager
        $hrManagerRole = Role::where('name', 'hr_manager')->where('guard_name', Rbac::GUARD)->first();
        if ($hrManagerRole) {
            $hrManagerRole->syncPermissions(array_merge($selfServicePermissions, [
                'team.view',
                'reports.performance',
                'departments.view',
                'users.view', 'users.create', 'users.edit',
                'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.delete', 'payroll.export',
                'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.export',
                'leaves.view', 'leaves.create', 'leaves.edit', 'leaves.approve', 'leaves.settings',
                'holidays.view', 'holidays.create', 'holidays.edit', 'holidays.delete',
            ]));
        }

        // HR Staff
        $hrRole = Role::where('name', 'hr')->where('guard_name', Rbac::GUARD)->first();
        if ($hrRole) {
            $hrRole->syncPermissions(array_merge($selfServicePermissions, [
                'departments.view',
                'users.view', 'users.create', 'users.edit',
                'attendance.view',
                'leaves.view',
                'holidays.view',
            ]));
        }

        // Sales Staff
        $salesRole = Role::where('name', 'sales')->where('guard_name', Rbac::GUARD)->first();
        if ($salesRole) {
            $salesRole->syncPermissions(array_merge($selfServicePermissions, [
                'sales_tracking.view',
                'customers.view',
                'leads.view',
                'leads.edit',
                'leads.notes.create',
                'followups.view',
                'followups.create',
                'followups.edit',
                'followups.delete',
            ]));
        }

        // Marketing Staff
        $marketingRole = Role::where('name', 'marketing')->where('guard_name', Rbac::GUARD)->first();
        if ($marketingRole) {
            $marketingRole->syncPermissions(array_merge($selfServicePermissions, [
                'tasks.view',
                'tasks.edit',
                'content.view',
                'content.create',
                'content.edit',
                'content.publish',
            ]));
        }

        // Development Staff
        $devRole = Role::where('name', 'development')->where('guard_name', Rbac::GUARD)->first();
        if ($devRole) {
            $devRole->syncPermissions(array_merge($selfServicePermissions, [
                'tasks.view',
                'tasks.edit',
            ]));
        }

        // Fallback manager role - remove sales permissions
        $managerRole = Role::where('name', 'manager')->where('guard_name', Rbac::GUARD)->first();
        if ($managerRole) {
            $managerRole->syncPermissions(array_merge($selfServicePermissions, [
                'team.view',
                'reports.performance',
                'tasks.view', 'tasks.create', 'tasks.edit',
                'content.view', 'content.create', 'content.edit', 'content.assign', 'content.review', 'content.publish', 'content.brands', 'content.accounts', 'content.reports',
                'expenses.view', 'expenses.approve',
                'attendance.view',
                'leaves.view', 'leaves.approve',
            ]));
        }

        $this->command?->info('Roles: '.implode(', ', Rbac::ROLES));
    }

    /**
     * Create the original departments.
     *
     * Idempotent, and never overwrites an existing row: a manager an admin has
     * already assigned must survive a re-seed.
     */
    protected function seedDepartments(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('departments')) {
            return;
        }

        foreach (self::DEFAULT_DEPARTMENTS as $name) {
            \App\Models\Department::firstOrCreate(['name' => $name]);
        }

        $this->command?->info(sprintf(
            'Departments: %d row(s) in the departments table.',
            \App\Models\Department::count()
        ));
    }

    /**
     * Seed the default data scope for every role and every scoped module.
     *
     * Idempotent: existing rows are updated in place, never duplicated, so
     * re-running the seeder after an admin has customised the matrix does not
     * silently reset their choices.
     *
     * Defaults: admin = all, every other role = own.
     */
    protected function seedDataScopes(): void
    {
        $scopedModules = array_keys(config('rbac.owner_columns', []));

        if ($scopedModules === []) {
            return;
        }
        

        foreach (Rbac::ROLES as $roleName) {
            $isManager = ($roleName === 'manager' || str_ends_with($roleName, '_manager'));
            $scope = $roleName === Rbac::ADMIN_ROLE
                ? \App\Rbac\DataScope::ALL
                : ($isManager ? \App\Rbac\DataScope::TEAM : \App\Rbac\DataScope::OWN);

            foreach ($scopedModules as $module) {
                \Illuminate\Support\Facades\DB::table('role_module_scopes')->updateOrInsert(
                    ['role_name' => $roleName, 'module' => $module],
                    ['scope' => $scope, 'updated_at' => now(), 'created_at' => now()],
                );
            }
        }

        $this->command?->info(sprintf(
            'Data scopes: %d role(s) x %d scoped module(s).',
            count(Rbac::ROLES),
            count($scopedModules)
        ));
    }

    /**
     * Assign the admin role to the configured e-mails, and reconcile the
     * legacy users.role mirror for every user.
     */
    protected function assignAdminRoles(): void
    {
        $missing = [];

        foreach (self::ADMIN_EMAILS as $email) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                $missing[] = $email;

                continue;
            }

            if (! $user->hasRole(Rbac::ADMIN_ROLE)) {
                $user->assignRbacRole(Rbac::ADMIN_ROLE);
                $this->command?->info("Assigned admin to {$email}.");
            }
        }

        if ($missing !== []) {
            // Fail loudly rather than silently seeding zero admins. Never create
            // the user automatically.
            throw new \RuntimeException(sprintf(
                'Cannot assign the admin role: user(s) not found for %s. '
                .'Create the user first, then re-run this seeder. '
                .'The seeder will not create users.',
                implode(', ', $missing)
            ));
        }

        $this->reconcileLegacyRoleColumn();
    }

    /**
     * Migrate legacy users.role values onto Spatie roles.
     *
     * Values with no mapping are reported, never guessed.
     */
    protected function reconcileLegacyRoleColumn(): void
    {
        $map = Rbac::legacyRoleMap();
        $unmapped = [];

        foreach (User::with('roles')->get() as $user) {
            $legacy = $user->getAttribute('role');
            $current = $user->rbacRoleName();

            // Already consistent: nothing to do.
            if ($legacy === $current) {
                continue;
            }

            if ($legacy === null || $legacy === '') {
                // No legacy role to migrate from. Spatie is authoritative, so
                // repair the mirror to match whatever Spatie says.
                if ($current !== null) {
                    $user->forceFill(['role' => $current])->saveQuietly();
                    $this->command?->info("Repaired empty legacy role for {$user->email} to '{$current}'.");
                }

                continue;
            }

            if (! isset($map[$legacy])) {
                $unmapped[$legacy] = ($unmapped[$legacy] ?? 0) + 1;

                continue;
            }

            $mapped = $map[$legacy];

            if ($user->hasRole($mapped)) {
                // Spatie already has it; just repair the mirror.
                $user->forceFill(['role' => $mapped])->saveQuietly();

                continue;
            }

            $user->assignRbacRole($mapped);
            $this->command?->info("Migrated legacy role '{$legacy}' to '{$mapped}' for {$user->email}.");
        }

        foreach ($unmapped as $value => $count) {
            $this->command?->warn(sprintf(
                'UNMAPPED legacy users.role value "%s" (%d user(s)). '
                .'No Spatie role was assigned. Decide manually: '
                .'add the mapping to Rbac::legacyRoleMap().',
                $value,
                $count
            ));
        }
    }

    /**
     * Drop the Spatie permission cache so new permissions are picked up.
     */
    protected function flushCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Print a short admin summary for verification.
     */
    protected function reportAdmins(): void
    {
        // Must select * — reading the 'role' mirror needs the column loaded.
        $admins = User::role(Rbac::ADMIN_ROLE)->get();

        foreach ($admins as $admin) {
            $this->command?->line(sprintf(
                '  admin: %s <%s> legacy role=%s',
                $admin->name,
                $admin->email,
                var_export($admin->getAttribute('role'), true)
            ));
        }

        $this->command?->line(sprintf('  total permission rows: %d', DB::table('permissions')->count()));
    }
}