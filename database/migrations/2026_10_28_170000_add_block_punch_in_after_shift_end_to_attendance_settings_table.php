<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->boolean('block_punch_in_after_shift_end')->default(true)->after('shift_end_time');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn('block_punch_in_after_shift_end');
        });
    }
};
