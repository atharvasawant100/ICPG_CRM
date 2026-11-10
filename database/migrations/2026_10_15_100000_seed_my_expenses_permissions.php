<?php

use App\Rbac\Rbac;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create permissions
        $permissions = ['my_expenses.view', 'my_expenses.create'];
        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => Rbac::GUARD,
            ]);
        }

        // 2. Assign to all non-admin roles (manager & EMPs)
        $roles = ['manager', 'sales', 'marketing', 'development', 'hr'];
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', Rbac::GUARD)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // 3. Clear permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = ['my_expenses.view', 'my_expenses.create'];
        $roles = ['manager', 'sales', 'marketing', 'development', 'hr'];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', Rbac::GUARD)->first();
            if ($role) {
                $role->revokePermissionTo($permissions);
            }
        }

        Permission::whereIn('name', $permissions)->where('guard_name', Rbac::GUARD)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
