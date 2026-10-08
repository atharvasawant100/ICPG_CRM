<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // sales | marketing | development | hr — nullable so existing users stay valid.
            $table->string('department')->nullable()->after('role');

            // Reporting line, used by the "team" data scope.
            $table->unsignedBigInteger('manager_id')->nullable()->after('department');

            $table->boolean('is_active')->default(true)->after('manager_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('manager_id');
            $table->index('department');

            $table->foreign('manager_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropIndex(['manager_id']);
            $table->dropIndex(['department']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['department', 'manager_id', 'is_active']);
        });
    }
};