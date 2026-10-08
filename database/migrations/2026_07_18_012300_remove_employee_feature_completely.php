<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Permanently deletes employee accounts and cascaded related records,
     * then removes employee-only schema.
     */
    public function up(): void
    {
        // Delete employee accounts (cascades remove related records via FKs)
        DB::table('users')->where('role', 'employee')->delete();

        Schema::dropIfExists('project_employees');

        if (Schema::hasColumn('leads', 'assigned_to')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }
            if (Schema::hasColumn('users', 'suspended_until')) {
                $table->dropColumn('suspended_until');
            }
        });

        // Restrict role to admin only.
        // Guarded by driver: MySQL/MariaDB needs the raw statement to narrow the
        // ENUM, but SQLite has no ENUM type and rejects "MODIFY COLUMN".
        // On SQLite the original VARCHAR column is left as-is, which is fine.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin') NOT NULL DEFAULT 'admin'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee'");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('role');
            $table->timestamp('suspended_until')->nullable()->after('suspended_at');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('source')->constrained('users')->onDelete('set null');
        });

        Schema::create('project_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('social_media_projects')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['project_id', 'employee_id']);
        });
    }
};
