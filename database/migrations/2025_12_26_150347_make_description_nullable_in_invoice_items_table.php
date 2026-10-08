<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * (invoice_items.description is already nullable in create migration;
     * this keeps older DBs in sync.)
     */
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // NOT NULL fails if any NULL rows exist
        DB::table('invoice_items')->whereNull('description')->update(['description' => '']);

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
        });
    }
};
