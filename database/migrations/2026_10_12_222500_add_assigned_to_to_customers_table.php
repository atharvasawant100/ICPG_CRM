<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'assigned_to')) {
                $table->foreignId('assigned_to')
                    ->nullable()
                    ->after('status')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        // Initialize assigned_to with created_by for existing records
        DB::table('customers')
            ->whereNull('assigned_to')
            ->update(['assigned_to' => DB::raw('created_by')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            }
        });
    }
};
