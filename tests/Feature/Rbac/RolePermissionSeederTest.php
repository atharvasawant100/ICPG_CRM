<?php

namespace Tests\Feature\Rbac;

use App\Models\User;
use App\Rbac\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed RBAC with the configured admin user present.
     *
     * The real database always has that user; the seeder refuses to invent it.
     */
    protected function seedRbac(): void
    {
        $this->ensureAdminUserExists();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function ensureAdminUserExists(): User
    {
        return User::firstOrCreate(
            ['email' => RolePermissionSeeder::ADMIN_EMAILS[0]],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'role' => 'admin'],
        );
    }

    protected function makeUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * The seeder is idempotent: running it repeatedly must not duplicate
     * permissions or roles, and must not fail.
     */
    public function test_seeder_is_idempotent(): void
    {
        $this->seedRbac();
        $permissionsAfterFirst = Permission::count();
        $rolesAfterFirst = Role::count();

        $this->seedRbac();
        $this->seedRbac();

        $this->assertSame($permissionsAfterFirst, Permission::count(), 'Permissions were duplicated.');
        $this->assertSame($rolesAfterFirst, Role::count(), 'Roles were duplicated.');
        $this->assertSame(count(Rbac::permissions()), Permission::count());
        $this->assertCount(count(Rbac::ROLES), Role::all());
    }

    /**
     * Every declared permission exists with the web guard.
     */
    public function test_all_declared_permissions_are_created(): void
    {
        $this->seedRbac();

        foreach (Rbac::permissions() as $name) {
            $this->assertDatabaseHas('permissions', [
                'name' => $name,
                'guard_name' => Rbac::GUARD,
            ]);
        }
    }

    /**
     * The admin role exists and holds no explicit permissions: admin access is
     * granted by Gate::before, not by syncing rows.
     */
    public function test_admin_role_has_no_explicit_permissions(): void
    {
        $this->seedRbac();

        $admin = Role::findByName(Rbac::ADMIN_ROLE, Rbac::GUARD);

        $this->assertCount(0, $admin->permissions, 'Admin should rely on Gate::before, not synced permissions.');
    }

    /**
     * Non-admin roles are created empty, ready for later phases.
     */
    public function test_other_roles_are_created_without_permissions(): void
    {
        $this->seedRbac();

        foreach (['manager', 'sales', 'marketing', 'development', 'hr'] as $name) {
            $role = Role::findByName($name, Rbac::GUARD);
            $this->assertCount(0, $role->permissions, "Role {$name} should start empty.");
        }
    }

    /**
     * The admin passes can() for every seeded permission.
     */
    public function test_admin_passes_can_for_every_seeded_permission(): void
    {
        $this->seedRbac();

        $admin = $this->makeUser();
        $admin->assignRbacRole(Rbac::ADMIN_ROLE);

        foreach (Rbac::permissions() as $permission) {
            $this->assertTrue(
                $admin->can($permission),
                "Admin should be able to {$permission}."
            );
        }
    }

    /**
     * Admin must never be locked out by a permission created after seeding.
     * This is the regression guard for Gate::before.
     */
    public function test_admin_passes_can_for_a_permission_created_later(): void
    {
        $this->seedRbac();

        $admin = $this->makeUser();
        $admin->assignRbacRole(Rbac::ADMIN_ROLE);

        $brandNew = 'future_module.do_something';

        $nonAdmin = $this->makeUser();
        $nonAdmin->assignRbacRole('sales');

        // Before the permission exists: the non-admin is denied, and the admin is
        // allowed purely by Gate::before. This is the baseline.
        $this->assertFalse(
            Gate::forUser($nonAdmin->fresh())->allows($brandNew),
            'Non-admin must not have an undefined permission.'
        );

        Permission::create(['name' => $brandNew, 'guard_name' => Rbac::GUARD]);

        // Refresh the permission cache the same way the seeder does, so this
        // mirrors a real "new permission deployed to production" flow.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue(
            Gate::forUser($admin->fresh())->allows($brandNew),
            'Admin must automatically gain any newly created permission.'
        );

        // Crucially, the new permission must NOT leak to other roles.
        $this->assertFalse(
            Gate::forUser($nonAdmin->fresh())->allows($brandNew),
            'A new permission must not leak to non-admin roles.'
        );
    }

    /**
     * Gate::before must return null (not false) for non-admins, so that the
     * real permission checks still run for them.
     */
    public function test_non_admin_gate_before_does_not_short_circuit(): void
    {
        $this->seedRbac();

        $user = $this->makeUser();

        $this->assertFalse($user->can('users.view'));
        $this->assertFalse($user->can('leads.delete'));
    }

    /**
     * A user with no role at all fails permission checks.
     */
    public function test_user_with_no_role_fails_permission_checks(): void
    {
        $this->seedRbac();

        $user = $this->makeUser();

        $this->assertFalse($user->hasRole(Rbac::ADMIN_ROLE));
        $this->assertFalse($user->can('users.view'));
        $this->assertFalse($user->can('dashboard.view'));
        $this->assertFalse($user->isAdmin());
    }

    /**
     * A user holding an empty non-admin role still fails, since those roles
     * intentionally carry no permissions in this phase.
     */
    public function test_user_with_empty_role_fails_permission_checks(): void
    {
        $this->seedRbac();

        $user = $this->makeUser();
        $user->assignRbacRole('sales');

        $this->assertTrue($user->hasRole('sales'));
        $this->assertFalse($user->can('users.view'));
        $this->assertFalse($user->isAdmin());
    }

    /**
     * A granted permission works for a non-admin, proving Gate::before is not
     * simply denying everything.
     */
    public function test_granted_permission_passes_for_non_admin(): void
    {
        $this->seedRbac();

        $user = $this->makeUser();
        $user->assignRbacRole('sales');

        $role = Role::findByName('sales', Rbac::GUARD);
        $role->givePermissionTo(Permission::findByName('leads.view', Rbac::GUARD));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = $user->fresh();

        $this->assertTrue(
            $user->can('leads.view'),
            'A permission granted to the user\'s role must pass.'
        );
        $this->assertFalse($user->can('leads.delete'));
        $this->assertFalse($user->isAdmin());
    }

    /**
     * The configured admin e-mail receives the admin role.
     */
    public function test_configured_admin_email_receives_admin_role(): void
    {
        $admin = $this->makeUser(['email' => RolePermissionSeeder::ADMIN_EMAILS[0]]);

        $this->seedRbac();

        $this->assertTrue($admin->fresh()->hasRole(Rbac::ADMIN_ROLE));
    }

    /**
     * A missing admin user must fail loudly and must not create the user.
     *
     * Note: this deliberately does NOT call ensureAdminUserExists().
     */
    public function test_seeder_fails_when_admin_user_missing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('admin@iconicpages.com');

        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * A failed seeder run must not invent the missing user, and must not leave
     * a half-built admin assignment behind.
     */
    public function test_seeder_does_not_create_users_when_admin_missing(): void
    {
        $this->assertDatabaseCount('users', 0);

        try {
            $this->seed(RolePermissionSeeder::class);
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertSame(0, User::count(), 'The seeder must never create users.');
        $this->assertSame(0, DB::table('model_has_roles')->count(), 'No role should have been assigned.');
    }

    /**
     * Gate resolves permissions for the admin through the provider hook.
     */
    public function test_gate_is_registered(): void
    {
        $this->seedRbac();

        $admin = $this->makeUser();
        $admin->assignRbacRole(Rbac::ADMIN_ROLE);

        $this->actingAs($admin);

        $this->assertTrue(Gate::allows('users.view'));
        $this->assertTrue(Gate::allows('expenses.approve'));
        $this->assertTrue(Gate::allows('audit_logs.view'));
    }
}