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

    public function run(): void
    {
        $this->seedPermissions();
        $this->seedRoles();
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

        $this->command?->info('Roles: '.implode(', ', Rbac::ROLES));
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