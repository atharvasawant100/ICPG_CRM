<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'rework_notes')) {
                $table->text('rework_notes')->nullable()->after('escalated_at');
            }
            if (! Schema::hasColumn('tasks', 'rework_count')) {
                $table->unsignedInteger('rework_count')->default(0)->after('rework_notes');
            }
            if (! Schema::hasColumn('tasks', 'rework_by')) {
                $table->foreignId('rework_by')->nullable()->after('rework_count')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('tasks', 'rework_at')) {
                $table->timestamp('rework_at')->nullable()->after('rework_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'rework_at')) {
                $table->dropColumn('rework_at');
            }
            if (Schema::hasColumn('tasks', 'rework_by')) {
                $table->dropForeign(['rework_by']);
                $table->dropColumn('rework_by');
            }
            if (Schema::hasColumn('tasks', 'rework_count')) {
                $table->dropColumn('rework_count');
            }
            if (Schema::hasColumn('tasks', 'rework_notes')) {
                $table->dropColumn('rework_notes');
            }
        });
    }
};

