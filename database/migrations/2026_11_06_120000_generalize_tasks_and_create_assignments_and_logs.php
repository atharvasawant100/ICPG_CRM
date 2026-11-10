<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modify or add columns on `tasks` table
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'start_date')) {
                $table->date('start_date')->nullable()->after('priority');
            }
            if (! Schema::hasColumn('tasks', 'expected_outcomes')) {
                $table->text('expected_outcomes')->nullable()->after('description');
            }
            if (! Schema::hasColumn('tasks', 'progress')) {
                $table->unsignedTinyInteger('progress')->default(0)->after('status');
            }
            if (! Schema::hasColumn('tasks', 'review_required')) {
                $table->boolean('review_required')->default(false)->after('progress');
            }
        });

        // Convert enum columns to VARCHAR for flexibility across statuses & priorities
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `tasks` MODIFY COLUMN `priority` VARCHAR(30) NOT NULL DEFAULT 'medium'");
            DB::statement("ALTER TABLE `tasks` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'open'");
        }

        // Migrate existing legacy data to generalized vocabulary
        DB::table('tasks')->where('priority', 'urgent')->update(['priority' => 'critical']);
        DB::table('tasks')->where('status', 'pending')->update(['status' => 'open']);

        // 2. Create `task_assignments` table for delegation & ownership history
        if (! Schema::hasTable('task_assignments')) {
            Schema::create('task_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
                $table->text('notes')->nullable();
                $table->timestamp('assigned_at')->useCurrent();
                $table->timestamps();

                $table->index(['task_id', 'assigned_at']);
                $table->index('assigned_to');
            });
        }

        // 3. Create `task_activity_logs` table for audit & event tracking
        if (! Schema::hasTable('task_activity_logs')) {
            Schema::create('task_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 50); // created, delegated, status_changed, updated
                $table->string('field_name', 50)->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->text('comment')->nullable();
                $table->timestamps();

                $table->index(['task_id', 'created_at']);
            });
        }

        // 4. Backfill initial assignment record for any existing tasks without one
        $existingTasks = DB::table('tasks')->select('id', 'assigned_by', 'assigned_to', 'created_at')->get();
        foreach ($existingTasks as $task) {
            $hasAssignment = DB::table('task_assignments')->where('task_id', $task->id)->exists();
            if (! $hasAssignment && $task->assigned_to) {
                DB::table('task_assignments')->insert([
                    'task_id' => $task->id,
                    'assigned_by' => $task->assigned_by ?? $task->assigned_to,
                    'assigned_to' => $task->assigned_to,
                    'notes' => 'Initial task assignment',
                    'assigned_at' => $task->created_at ?? now(),
                    'created_at' => $task->created_at ?? now(),
                    'updated_at' => $task->created_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_activity_logs');
        Schema::dropIfExists('task_assignments');

        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'review_required')) {
                $table->dropColumn('review_required');
            }
            if (Schema::hasColumn('tasks', 'progress')) {
                $table->dropColumn('progress');
            }
            if (Schema::hasColumn('tasks', 'expected_outcomes')) {
                $table->dropColumn('expected_outcomes');
            }
            if (Schema::hasColumn('tasks', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};

