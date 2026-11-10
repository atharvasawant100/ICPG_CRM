<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            // Standard Earnings Breakdown
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('hra', 12, 2)->default(0); // House Rent Allowance
            $table->decimal('conveyance_allowance', 12, 2)->default(0);
            $table->decimal('special_allowance', 12, 2)->default(0);
            $table->decimal('other_allowances', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);

            // Standard Deductions Breakdown
            $table->decimal('provident_fund', 12, 2)->default(0); // PF
            $table->decimal('professional_tax', 12, 2)->default(0); // PT
            $table->decimal('tds', 12, 2)->default(0); // Tax / TDS
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);

            // Calculated Net
            $table->decimal('net_salary', 12, 2)->default(0);

            // Payment & Bank Details
            $table->string('payment_method', 50)->default('bank_transfer'); // bank_transfer, cheque, cash
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('ifsc_code', 20)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
