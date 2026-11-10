<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 0. Ensure leads table has assigned_to column
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('source')->constrained('users')->onDelete('set null');
            }
        });

        // 1. Widen status column on leads to VARCHAR(50) for maximum flexibility
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE leads MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'new'");
        }

        // 2. Create lead_status_logs table
        if (! Schema::hasTable('lead_status_logs')) {
            Schema::create('lead_status_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
                $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->string('old_status')->nullable();
                $table->string('new_status')->index();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['lead_id', 'created_at']);
            });
        }

        // 3. Add meeting fields to lead_followups
        Schema::table('lead_followups', function (Blueprint $table) {
            if (! Schema::hasColumn('lead_followups', 'meeting_link')) {
                $table->string('meeting_link', 500)->nullable()->after('followup_type');
            }
            if (! Schema::hasColumn('lead_followups', 'agenda')) {
                $table->text('agenda')->nullable()->after('meeting_link');
            }
            if (! Schema::hasColumn('lead_followups', 'meeting_platform')) {
                $table->string('meeting_platform', 50)->default('meet')->after('meeting_link');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            if (Schema::hasColumn('lead_followups', 'meeting_platform')) {
                $table->dropColumn('meeting_platform');
            }
            if (Schema::hasColumn('lead_followups', 'agenda')) {
                $table->dropColumn('agenda');
            }
            if (Schema::hasColumn('lead_followups', 'meeting_link')) {
                $table->dropColumn('meeting_link');
            }
        });

        Schema::dropIfExists('lead_status_logs');
    }
};
