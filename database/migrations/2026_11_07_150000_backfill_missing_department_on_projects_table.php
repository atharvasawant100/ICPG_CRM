<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        // 1. Backfill department from tasks associated with the project
        $projectsWithNullDept = DB::table('projects')
            ->whereNull('department')
            ->orWhere('department', '')
            ->pluck('id');

        foreach ($projectsWithNullDept as $projectId) {
            $taskDept = DB::table('tasks')
                ->where('project_id', $projectId)
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->value('department');

            if ($taskDept) {
                DB::table('projects')
                    ->where('id', $projectId)
                    ->update(['department' => strtolower(trim((string) $taskDept))]);
                continue;
            }

            // 2. Fall back to project creator's department
            $creatorId = DB::table('projects')->where('id', $projectId)->value('created_by');
            if ($creatorId) {
                $userDept = DB::table('users')->where('id', $creatorId)->value('department');
                if ($userDept) {
                    DB::table('projects')
                        ->where('id', $projectId)
                        ->update(['department' => strtolower(trim((string) $userDept))]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};

