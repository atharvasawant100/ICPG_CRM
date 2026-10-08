<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * users.role was created as ENUM('admin','employee') by
     * 2025_12_18_053558 and then narrowed to ENUM('admin') by
     * 2026_07_18_012300, with NOT NULL DEFAULT 'admin'.
     *
     * That column cannot hold the new role names (manager, sales, marketing,
     * development, hr), so it cannot be kept in sync with Spatie roles. Worse,
     * DEFAULT 'admin' means any user inserted without an explicit role becomes
     * an administrator.
     *
     * Widen it to a nullable VARCHAR so the legacy column can mirror the Spatie
     * role name, and so the safe default is "no role".
     *
     * Widening a column type is non-destructive: every existing 'admin' value
     * is preserved.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NULL DEFAULT NULL');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 50)->nullable()->default(null)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * NOTE: reverting narrows the column back to ENUM('admin'), which cannot
     * represent the new role names. Any non-'admin' value is normalised to
     * 'admin' first, so rolling this back after non-admin roles are in use
     * will collapse those roles. Spatie's own role tables are untouched.
     */
    public function down(): void
    {
        DB::table('users')->whereNotNull('role')->where('role', '!=', 'admin')->update(['role' => 'admin']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin') NOT NULL DEFAULT 'admin'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 50)->nullable()->default(null)->change();
            });
        }
    }
};