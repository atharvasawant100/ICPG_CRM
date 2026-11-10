<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalary extends Model
{
    use Auditable, HasFactory;

    protected $auditModule = 'payroll';

    protected $fillable = [
        'user_id',
        'basic_salary',
        'hra',
        'conveyance_allowance',
        'special_allowance',
        'other_allowances',
        'gross_salary',
        'provident_fund',
        'professional_tax',
        'tds',
        'other_deductions',
        'total_deductions',
        'net_salary',
        'payment_method',
        'bank_name',
        'account_number',
        'ifsc_code',
        'pan_number',
        'notes',
        'approval_status',
        'submitted_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'hra' => 'decimal:2',
            'conveyance_allowance' => 'decimal:2',
            'special_allowance' => 'decimal:2',
            'other_allowances' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'provident_fund' => 'decimal:2',
            'professional_tax' => 'decimal:2',
            'tds' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Check if the salary structure is approved by Admin.
     */
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check if the salary structure is awaiting Admin approval.
     */
    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending_approval';
    }

    /**
     * Check if the salary structure has been rejected by Admin.
     */
    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending_approval');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $decimals = [
                'basic_salary', 'hra', 'conveyance_allowance', 'special_allowance', 'other_allowances',
                'gross_salary', 'provident_fund', 'professional_tax', 'tds', 'other_deductions',
                'total_deductions', 'net_salary',
            ];
            foreach ($decimals as $col) {
                if ($model->{$col} === null || $model->{$col} === '') {
                    $model->{$col} = 0;
                }
            }
        });
    }

    /**
     * Recompute and set gross_salary, total_deductions, and net_salary.
     */
    public function computeTotals(): self
    {
        $this->basic_salary = (float) ($this->basic_salary ?? 0);
        $this->hra = (float) ($this->hra ?? 0);
        $this->conveyance_allowance = (float) ($this->conveyance_allowance ?? 0);
        $this->special_allowance = (float) ($this->special_allowance ?? 0);
        $this->other_allowances = (float) ($this->other_allowances ?? 0);

        $this->provident_fund = (float) ($this->provident_fund ?? 0);
        $this->professional_tax = (float) ($this->professional_tax ?? 0);
        $this->tds = (float) ($this->tds ?? 0);
        $this->other_deductions = (float) ($this->other_deductions ?? 0);

        $gross = $this->basic_salary
            + $this->hra
            + $this->conveyance_allowance
            + $this->special_allowance
            + $this->other_allowances;

        $deductions = $this->provident_fund
            + $this->professional_tax
            + $this->tds
            + $this->other_deductions;

        $net = max(0, $gross - $deductions);

        $this->gross_salary = round($gross, 2);
        $this->total_deductions = round($deductions, 2);
        $this->net_salary = round($net, 2);

        return $this;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
