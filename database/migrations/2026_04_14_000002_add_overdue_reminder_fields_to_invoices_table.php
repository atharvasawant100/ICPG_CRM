<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('overdue_reminder_last_sent_at')->nullable()->after('email_sent_count');
            $table->unsignedInteger('overdue_reminder_sent_count')->default(0)->after('overdue_reminder_last_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['overdue_reminder_last_sent_at', 'overdue_reminder_sent_count']);
        });
    }
};

