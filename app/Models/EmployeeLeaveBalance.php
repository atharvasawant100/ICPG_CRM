<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use App\Rbac\Rbac;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveBalance extends Model
{
    use Auditable, HasFactory;

    protected $auditModule = 'leaves';

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'year',
        'allocated_days',
        'used_days',
        'pending_days',
        'remaining_days',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'allocated_days' => 'decimal:1',
            'used_days' => 'decimal:1',
            'pending_days' => 'decimal:1',
            'remaining_days' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function recompute(): self
    {
        $this->remaining_days = max(0, (float) $this->allocated_days - (float) $this->used_days);
        return $this;
    }

    /**
     * Get or create balance record for a user & leave type for a year.
     */
    public static function getOrCreateForUser(int $userId, int $leaveTypeId, int $year): self
    {
        $balance = static::where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (! $balance) {
            $leaveType = LeaveType::find($leaveTypeId);
            $allocated = $leaveType ? (float) $leaveType->days_allowed_per_year : 0.0;

            $balance = static::create([
                'user_id' => $userId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
                'allocated_days' => $allocated,
                'used_days' => 0.0,
                'pending_days' => 0.0,
                'remaining_days' => $allocated,
            ]);
        }

        return $balance;
    }

    /**
     * Initialize balances for all active non-admin employees for a year.
     */
    public static function initializeForYear(int $year): void
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = User::active()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Rbac::ADMIN_ROLE))
            ->get();

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $type) {
                static::firstOrCreate(
                    [
                        'user_id' => $employee->id,
                        'leave_type_id' => $type->id,
                        'year' => $year,
                    ],
                    [
                        'allocated_days' => (float) $type->days_allowed_per_year,
                        'used_days' => 0.0,
                        'pending_days' => 0.0,
                        'remaining_days' => (float) $type->days_allowed_per_year,
                    ]
                );
            }
        }
    }
}
