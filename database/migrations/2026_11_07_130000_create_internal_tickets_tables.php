<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Internal Tickets table
        if (! Schema::hasTable('internal_tickets')) {
            Schema::create('internal_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number', 30)->unique();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
                $table->string('category', 50); // manager_blocker, unfair_blame, missing_resources, workplace_concern, other
                $table->string('priority', 20)->default('medium'); // low, medium, high, urgent
                $table->string('subject', 255);
                $table->text('description');
                $table->string('status', 30)->default('open'); // open, in_review, resolved, closed
                $table->boolean('is_confidential')->default(true);
                $table->text('admin_notes')->nullable();
                $table->text('admin_response')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['status', 'priority']);
                $table->index('category');
            });
        }

        // 2. Internal Ticket Messages (Two-way communication thread between employee and admin)
        if (! Schema::hasTable('internal_ticket_messages')) {
            Schema::create('internal_ticket_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('internal_ticket_id')->constrained('internal_tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('message');
                $table->boolean('is_admin_note')->default(false);
                $table->timestamps();

                $table->index(['internal_ticket_id', 'created_at']);
            });
        }

        // 3. Internal Ticket Attachments (Screenshots, email proof, chat logs)
        if (! Schema::hasTable('internal_ticket_attachments')) {
            Schema::create('internal_ticket_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('internal_ticket_id')->constrained('internal_tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('file_name');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size');
                $table->string('mime_type', 100)->nullable();
                $table->timestamps();

                $table->index('internal_ticket_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_ticket_attachments');
        Schema::dropIfExists('internal_ticket_messages');
        Schema::dropIfExists('internal_tickets');
    }
};

