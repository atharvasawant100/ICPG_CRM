<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            
            // Standard shift schedule
            $table->string('shift_start_time', 10)->default('09:30');
            $table->string('shift_end_time', 10)->default('18:30');
            
            // Late mark & half-day login thresholds
            $table->string('late_mark_after', 10)->default('10:15');
            $table->string('half_day_login_after', 10)->default('13:00');
            
            // Working hours thresholds
            $table->decimal('full_day_min_hours', 4, 2)->default(8.00);
            $table->decimal('half_day_min_hours', 4, 2)->default(4.00);
            $table->boolean('auto_half_day_on_short_hours')->default(true);
            
            // Auto punch-out configuration for forgotten punch-outs
            $table->boolean('enable_auto_punch_out')->default(true);
            $table->string('auto_punch_out_time', 10)->default('19:00');
            $table->string('auto_punch_out_mode', 20)->default('fixed_time'); // fixed_time, shift_duration
            
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
