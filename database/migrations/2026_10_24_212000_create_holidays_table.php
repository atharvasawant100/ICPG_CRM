<?php

use App\Rbac\Rbac;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->date('date')->unique();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Seed holiday permissions
        $permissions = [
            'holidays.view',
            'holidays.create',
            'holidays.edit',
            'holidays.delete',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => Rbac::GUARD,
            ]);
        }

        // Grant to HR role
        $hrRole = Role::where('name', 'hr')->where('guard_name', Rbac::GUARD)->first();
        if ($hrRole) {
            $hrRole->givePermissionTo($permissions);
        }

        // Clear permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');

        $permissions = [
            'holidays.view',
            'holidays.create',
            'holidays.edit',
            'holidays.delete',
        ];

        $hrRole = Role::where('name', 'hr')->where('guard_name', Rbac::GUARD)->first();
        if ($hrRole) {
            $hrRole->revokePermissionTo($permissions);
        }

        Permission::whereIn('name', $permissions)->where('guard_name', Rbac::GUARD)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
