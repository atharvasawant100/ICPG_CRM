<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->json('reminders_sent')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->dropColumn('reminders_sent');
        });
    }
};
