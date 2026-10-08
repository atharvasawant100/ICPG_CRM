<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-role, per-module data scope (own / team / all).
 *
 * This replaces the hard-coded scope map in config/rbac.php. The config stays in
 * place as a fallback so the app still behaves correctly if this table is empty
 * or the lookup fails.
 *
 * Keyed on role_name rather than role_id: the role is what matters semantically,
 * the admin role has no permission rows to join through, and a scope survives the
 * role being recreated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_module_scopes', function (Blueprint $table) {
            $table->id();

            $table->string('role_name', 50);
            $table->string('module', 50);

            // Own records only.
            $table->enum('scope', ['own', 'team', 'all'])->default('own');

            $table->timestamps();

            $table->unique(['role_name', 'module'], 'role_module_scopes_unique');
            $table->index('module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_module_scopes');
    }
};