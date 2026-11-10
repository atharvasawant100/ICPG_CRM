<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payslip delivery tracking, mirroring invoices.email_sent_*.
     *
     * Stamped by SendPayslipEmail only after the transport accepts the message,
     * so these columns never claim delivery that bounced.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->timestamp('payslip_sent_at')->nullable()->after('payment_status');
            $table->unsignedInteger('payslip_sent_count')->default(0)->after('payslip_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['payslip_sent_at', 'payslip_sent_count']);
        });
    }
};
