<?php

namespace Tests\Feature\Rbac;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use App\Rbac\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Shared helpers for the RBAC enforcement tests.
 */
abstract class RbacTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed RBAC with the configured admin present, as the real DB does.
     */
    protected function seedRbac(): void
    {
        User::firstOrCreate(
            ['email' => RolePermissionSeeder::ADMIN_EMAILS[0]],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'role' => 'admin'],
        );

        $this->seed(RolePermissionSeeder::class);
    }

    protected function admin(array $attributes = []): User
    {
        $admin = User::factory()->create($attributes);
        $admin->assignRbacRole(Rbac::ADMIN_ROLE);

        return $admin->fresh();
    }

    protected function userWithPermissions(array $permissions, string $roleName = 'temp_role'): User
    {
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => Rbac::GUARD,
        ]);

        $role->givePermissionTo(
            Permission::whereIn('name', $permissions)->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create();
        $user->assignRbacRole($roleName);

        return $user->fresh();
    }

    protected function rolelessUser(): User
    {
        return User::factory()->create();
    }
}