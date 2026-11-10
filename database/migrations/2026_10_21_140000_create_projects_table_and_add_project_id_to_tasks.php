<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();
                $table->string('title', 150);
                $table->text('description')->nullable();
                $table->string('client_name', 150)->nullable();
                $table->string('department', 60)->nullable();
                $table->enum('status', ['active', 'completed', 'on_hold', 'archived'])->default('active');
                $table->string('color', 30)->default('indigo');
                $table->date('due_date')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('status');
                $table->index('department');
            });
        }

        if (! Schema::hasColumn('tasks', 'project_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->after('id')->constrained('projects')->nullOnDelete();
                $table->index('project_id');
            });
        }

        // Backfill existing project_name entries into projects table
        if (Schema::hasColumn('tasks', 'project_name')) {
            $existing = DB::table('tasks')
                ->whereNotNull('project_name')
                ->where('project_name', '!=', '')
                ->select('project_name', 'department')
                ->distinct()
                ->get();

            foreach ($existing as $item) {
                $trimmed = trim((string) $item->project_name);
                if ($trimmed === '') {
                    continue;
                }

                $existingProject = DB::table('projects')->where('title', $trimmed)->first();
                if (! $existingProject) {
                    $projectId = DB::table('projects')->insertGetId([
                        'title' => $trimmed,
                        'department' => $item->department,
                        'status' => 'active',
                        'color' => 'indigo',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $projectId = $existingProject->id;
                }

                DB::table('tasks')
                    ->where('project_name', $item->project_name)
                    ->whereNull('project_id')
                    ->update(['project_id' => $projectId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'project_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            });
        }

        Schema::dropIfExists('projects');
    }
};
