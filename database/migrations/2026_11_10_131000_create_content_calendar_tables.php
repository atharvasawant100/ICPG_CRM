<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Content Posts Table
        Schema::create('content_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->string('title');
            $table->string('post_type', 40)->default('single_image'); // single_image, video, carousel, reel_short, story, text_post, other
            $table->longText('caption')->nullable();
            $table->text('hashtags')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->string('timezone', 60)->default('Asia/Kolkata');
            $table->foreignId('content_creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->boolean('approval_required')->default(true);
            $table->string('status', 40)->default('idea'); // idea, planned, in_creation, pending_review, changes_requested, approved, scheduled, published, failed, cancelled
            $table->dateTime('published_at')->nullable();
            $table->string('published_url')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('publishing_notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->boolean('is_missed')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['brand_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index('status');
            $table->index('scheduled_at');
            $table->index('assigned_to');
            $table->index('reviewer_id');
            $table->index('is_missed');
        });

        // 2. Content Platform Schedules Table (Multi-platform publishing target entries)
        Schema::create('content_platform_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_post_id')->constrained('content_posts')->cascadeOnDelete();
            $table->foreignId('social_account_id')->nullable()->constrained('social_accounts')->nullOnDelete();
            $table->string('platform', 40);
            $table->longText('custom_caption')->nullable();
            $table->text('custom_hashtags')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->string('status', 40)->default('planned'); // planned, scheduled, published, failed, cancelled
            $table->dateTime('published_at')->nullable();
            $table->string('published_url')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->boolean('is_missed')->default(false);
            $table->timestamps();

            $table->index(['content_post_id', 'platform']);
            $table->index(['social_account_id', 'status']);
            $table->index('status');
            $table->index('scheduled_at');
        });

        // 3. Content Media Attachments Table
        Schema::create('content_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_post_id')->constrained('content_posts')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type', 100);
            $table->string('media_type', 30)->default('image'); // image, video, document, other
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('caption')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['content_post_id', 'sort_order']);
        });

        // 4. Content Approvals / Review History Table
        Schema::create('content_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_post_id')->constrained('content_posts')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 30); // approved, changes_requested
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['content_post_id', 'action']);
        });

        // 5. Content Activity Logs Table
        Schema::create('content_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_post_id')->constrained('content_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50); // created, updated, assigned, media_uploaded, media_removed, review_submitted, approved, changes_requested, scheduled, published, failed, missed, cancelled, rescheduled
            $table->string('field_name', 60)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['content_post_id', 'action']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_activity_logs');
        Schema::dropIfExists('content_approvals');
        Schema::dropIfExists('content_media');
        Schema::dropIfExists('content_platform_schedules');
        Schema::dropIfExists('content_posts');
    }
};

