<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'brand')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('brand')->nullable()->after('customer_id');
            });
        }

        if (Schema::hasTable('recurring_invoice_templates') && !Schema::hasColumn('recurring_invoice_templates', 'brand')) {
            Schema::table('recurring_invoice_templates', function (Blueprint $table) {
                $table->string('brand')->nullable()->after('customer_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'brand')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('brand');
            });
        }

        if (Schema::hasTable('recurring_invoice_templates') && Schema::hasColumn('recurring_invoice_templates', 'brand')) {
            Schema::table('recurring_invoice_templates', function (Blueprint $table) {
                $table->dropColumn('brand');
            });
        }
    }
};
