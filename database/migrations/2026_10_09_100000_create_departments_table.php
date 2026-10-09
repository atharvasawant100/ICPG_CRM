<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Departments become a real, editable list.
 *
 * Previously users.department was a hard-coded string limited to four values.
 * This table holds the list so an admin can add departments from the UI, and
 * so each department can have a manager.
 *
 * users.department stays a string (non-destructive, existing rows untouched);
 * it simply references departments.name. The RolePermissionSeeder seeds the
 * four original values so nothing is lost.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)->unique();
            $table->string('description')->nullable();

            // nullOnDelete: removing a manager must not delete the department.
            $table->foreignId('manager_id')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};