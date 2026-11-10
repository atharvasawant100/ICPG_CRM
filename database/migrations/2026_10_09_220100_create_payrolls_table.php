<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedSmallInteger('payroll_month'); // 1-12
            $table->unsignedSmallInteger('payroll_year'); // e.g. 2026
            $table->string('payroll_code', 50)->unique(); // e.g. PAY-202610-ICPG01

            // Earnings
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('hra', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0); // Combined other allowances
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);

            // Deductions
            $table->decimal('provident_fund', 12, 2)->default(0);
            $table->decimal('professional_tax', 12, 2)->default(0);
            $table->decimal('tds', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);

            // Net Pay
            $table->decimal('net_salary', 12, 2)->default(0);

            // Payment Information
            $table->string('payment_status', 20)->default('pending'); // pending, processed, paid
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('transaction_reference', 100)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'payroll_month', 'payroll_year']);
            $table->index(['payroll_year', 'payroll_month']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
