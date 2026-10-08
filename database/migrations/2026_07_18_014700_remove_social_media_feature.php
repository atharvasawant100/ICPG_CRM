<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Removes the Social Media feature schema entirely.
     */
    public function up(): void
    {
        Schema::dropIfExists('social_media_projects');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('social_media_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('email_notifications_enabled')->default(true);
            $table->time('notification_time')->default('09:00:00');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }
};
