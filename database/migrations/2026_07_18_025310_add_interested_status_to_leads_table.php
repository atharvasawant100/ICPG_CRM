<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new', 'contacted', 'interested', 'qualified', 'converted', 'lost') NOT NULL DEFAULT 'new'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('leads')->where('status', 'interested')->update(['status' => 'contacted']);
            DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new', 'contacted', 'qualified', 'converted', 'lost') NOT NULL DEFAULT 'new'");
        }
    }
};
