<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('department', 60)->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['assigned_to', 'status']);
            $table->index(['status', 'due_date']);
            $table->index('department');
        });

        Schema::create('task_work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->date('worked_date')->index();
            $table->string('status_before', 30)->nullable();
            $table->string('status_after', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['task_id', 'worked_date']);
            $table->index(['user_id', 'worked_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_work_logs');
        Schema::dropIfExists('tasks');
    }
};
