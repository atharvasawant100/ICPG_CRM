<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Task Help Requests table
        if (! Schema::hasTable('task_help_requests')) {
            Schema::create('task_help_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('helper_id')->constrained('users')->cascadeOnDelete();
                $table->text('message');
                $table->string('status', 30)->default('pending'); // pending, accepted, rejected, cancelled
                $table->text('response_notes')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();

                $table->index(['task_id', 'status']);
                $table->index(['helper_id', 'status']);
            });
        }

        // 2. Task Collaborators table
        if (! Schema::hasTable('task_collaborators')) {
            Schema::create('task_collaborators', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('role', 50)->default('collaborator'); // collaborator, contributor, reviewer
                $table->timestamps();

                $table->unique(['task_id', 'user_id']);
                $table->index('user_id');
            });
        }

        // 3. Task Comments table (with replies support)
        if (! Schema::hasTable('task_comments')) {
            Schema::create('task_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('task_comments')->cascadeOnDelete();
                $table->text('comment');
                $table->timestamps();

                $table->index(['task_id', 'created_at']);
            });
        }

        // 4. Task Attachments table
        if (! Schema::hasTable('task_attachments')) {
            Schema::create('task_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('file_name');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size');
                $table->string('mime_type', 100)->nullable();
                $table->timestamps();

                $table->index('task_id');
            });
        }

        // 5. Task Review Actions table
        if (! Schema::hasTable('task_review_actions')) {
            Schema::create('task_review_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 50); // submitted, approved, changes_requested
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['task_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_review_actions');
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_collaborators');
        Schema::dropIfExists('task_help_requests');
    }
};

