<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'is_escalated')) {
                $table->boolean('is_escalated')->default(false)->after('review_required');
            }
            if (! Schema::hasColumn('tasks', 'escalation_reason')) {
                $table->text('escalation_reason')->nullable()->after('is_escalated');
            }
            if (! Schema::hasColumn('tasks', 'escalated_by')) {
                $table->foreignId('escalated_by')->nullable()->after('escalation_reason')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('tasks', 'escalated_at')) {
                $table->timestamp('escalated_at')->nullable()->after('escalated_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'escalated_at')) {
                $table->dropColumn('escalated_at');
            }
            if (Schema::hasColumn('tasks', 'escalated_by')) {
                $table->dropForeign(['escalated_by']);
                $table->dropColumn('escalated_by');
            }
            if (Schema::hasColumn('tasks', 'escalation_reason')) {
                $table->dropColumn('escalation_reason');
            }
            if (Schema::hasColumn('tasks', 'is_escalated')) {
                $table->dropColumn('is_escalated');
            }
        });
    }
};

