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
        // 1. Brands Table
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('website')->nullable();
            $table->string('industry', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('color', 30)->default('indigo');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'is_active']);
            $table->index('name');
        });

        // 2. Social Accounts Table
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('platform', 40); // instagram, facebook, linkedin, twitter_x, youtube, pinterest, tiktok, threads, other
            $table->string('account_name');
            $table->string('username')->nullable(); // @handle or page ID
            $table->string('platform_account_id')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('connection_status', 30)->default('connected'); // connected, disconnected, pending
            $table->text('credentials')->nullable(); // Encrypted token / secrets
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['brand_id', 'platform']);
            $table->index('platform');
            $table->index('connection_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('brands');
    }
};

