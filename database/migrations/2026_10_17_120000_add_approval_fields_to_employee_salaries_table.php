<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->string('approval_status', 30)->default('approved')->after('notes');
            $table->foreignId('submitted_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('submitted_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approval_status',
                'submitted_by',
                'approved_by',
                'approved_at',
                'rejection_reason',
            ]);
        });
    }
};
