<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Leave Types configuration
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->decimal('days_allowed_per_year', 4, 1)->default(12.0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Employee Annual Leave Balances
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('year')->index();
            $table->decimal('allocated_days', 4, 1)->default(0);
            $table->decimal('used_days', 4, 1)->default(0);
            $table->decimal('pending_days', 4, 1)->default(0);
            $table->decimal('remaining_days', 4, 1)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'leave_type_id', 'year'], 'emp_leave_year_unique');
        });

        // 3. Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 4, 1)->default(1.0);
            $table->boolean('is_half_day')->default(false);
            $table->string('half_day_type', 20)->nullable(); // first_half, second_half
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, cancelled
            $table->foreignId('action_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_at')->nullable();
            $table->text('admin_remarks')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('employee_leave_balances');
        Schema::dropIfExists('leave_types');
    }
};
