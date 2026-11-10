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
        if (Schema::hasTable('tasks') && ! Schema::hasColumn('tasks', 'content_post_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreignId('content_post_id')->nullable()->after('project_id')->constrained('content_posts')->nullOnDelete();
                $table->string('content_task_type', 50)->nullable()->after('content_post_id'); // write_caption, create_graphic, edit_video, prepare_carousel, review_content, prepare_publishing_material
                $table->index('content_post_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'content_post_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['content_post_id']);
                $table->dropColumn(['content_post_id', 'content_task_type']);
            });
        }
    }
};

