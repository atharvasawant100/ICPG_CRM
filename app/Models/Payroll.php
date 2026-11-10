<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use Auditable, HasFactory;

    protected $auditModule = 'payroll';

    protected $fillable = [
        'user_id',
        'payroll_month',
        'payroll_year',
        'payroll_code',
        'basic_salary',
        'hra',
        'allowances',
        'bonus',
        'overtime_pay',
        'gross_salary',
        'provident_fund',
        'professional_tax',
        'tds',
        'other_deductions',
        'total_deductions',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'payslip_sent_at',
        'payslip_sent_count',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payroll_month' => 'integer',
            'payroll_year' => 'integer',
            'basic_salary' => 'decimal:2',
            'hra' => 'decimal:2',
            'allowances' => 'decimal:2',
            'bonus' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'provident_fund' => 'decimal:2',
            'professional_tax' => 'decimal:2',
            'tds' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'payment_date' => 'date',
            'payslip_sent_at' => 'datetime',
            'payslip_sent_count' => 'integer',
        ];
    }

    /**
     * Compute and assign gross_salary, total_deductions, and net_salary.
     */
    public function computeTotals(): self
    {
        $gross = (float) $this->basic_salary
            + (float) $this->hra
            + (float) $this->allowances
            + (float) $this->bonus
            + (float) $this->overtime_pay;

        $deductions = (float) $this->provident_fund
            + (float) $this->professional_tax
            + (float) $this->tds
            + (float) $this->other_deductions;

        $net = max(0, $gross - $deductions);

        $this->gross_salary = round($gross, 2);
        $this->total_deductions = round($deductions, 2);
        $this->net_salary = round($net, 2);

        return $this;
    }

    /**
     * Generate standard payroll code: PAY-YYYYMM-EMPXX
     */
    public static function generateCode(User $user, int $year, int $month): string
    {
        $period = sprintf('%04d%02d', $year, $month);
        $empCode = $user->emp_id ? str_replace('_', '', $user->emp_id) : 'U' . $user->id;

        $base = "PAY-{$period}-{$empCode}";
        $code = $base;
        $counter = 1;

        while (static::where('payroll_code', $code)->exists()) {
            $code = "{$base}-{$counter}";
            $counter++;
        }

        return $code;
    }

    /**
     * Formatted period: e.g. "October 2026"
     */
    public function getPeriodLabelAttribute(): string
    {
        return Carbon::createFromDate($this->payroll_year, $this->payroll_month, 1)->format('F Y');
    }

    public function getPdfFilename(): string
    {
        return 'payslip-' . $this->payroll_code . '.pdf';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeForPeriod($query, int $month, int $year)
    {
        return $query->where('payroll_month', $month)->where('payroll_year', $year);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }
}
