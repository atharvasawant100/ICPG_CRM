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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('designated_person', 255)->nullable()->after('status');
        });

        // Backfill designated_person with assigned user's name or creator's name
        $customers = DB::table('customers')->get();
        foreach ($customers as $c) {
            $userId = $c->assigned_to ?: $c->created_by;
            if ($userId) {
                $user = DB::table('users')->where('id', $userId)->first();
                if ($user) {
                    DB::table('customers')->where('id', $c->id)->update([
                        'designated_person' => $user->name,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('designated_person');
        });
    }
};
