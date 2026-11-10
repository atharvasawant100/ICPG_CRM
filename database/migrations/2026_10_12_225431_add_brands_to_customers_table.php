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
            $table->text('brands')->nullable()->after('company');
        });

        // Initialize brands from company for existing customers
        $customers = DB::table('customers')->whereNotNull('company')->get();
        foreach ($customers as $c) {
            if ($c->company) {
                DB::table('customers')->where('id', $c->id)->update([
                    'brands' => $c->company,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('brands');
        });
    }
};
