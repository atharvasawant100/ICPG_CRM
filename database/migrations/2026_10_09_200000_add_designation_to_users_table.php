<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores the job title shown on the Add/Edit User form.
 *
 * The form no longer has a Role field. Designation is what the admin picks,
 * and App\Rbac\Designation turns it into the Spatie role that actually decides
 * access, so the role itself is never mass-assigned from a form.
 *
 * Nullable and with no default, so existing users are untouched and simply have
 * no designation until one is set.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('designation', 100)->nullable()->after('department');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('designation');
        });
    }
};