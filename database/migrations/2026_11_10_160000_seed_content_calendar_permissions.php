<?php

use App\Rbac\Rbac;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $contentPermissions = [
            'content.view',
            'content.create',
            'content.edit',
            'content.delete',
            'content.assign',
            'content.review',
            'content.publish',
            'content.brands',
            'content.accounts',
            'content.reports',
        ];

        foreach ($contentPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => Rbac::GUARD]);
        }

        // Grant to marketing role
        $marketingRole = Role::where('name', 'marketing')->where('guard_name', Rbac::GUARD)->first();
        if ($marketingRole) {
            $marketingRole->givePermissionTo([
                'content.view',
                'content.create',
                'content.edit',
                'content.publish',
            ]);
        }

        // Grant to marketing_manager role
        $marketingManagerRole = Role::where('name', 'marketing_manager')->where('guard_name', Rbac::GUARD)->first();
        if ($marketingManagerRole) {
            $marketingManagerRole->givePermissionTo($contentPermissions);
        }

        // Grant to admin role
        $adminRole = Role::where('name', 'admin')->where('guard_name', Rbac::GUARD)->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($contentPermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep permissions on down
    }
};

