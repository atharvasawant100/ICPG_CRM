<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use Auditable, HasFactory;

    protected $auditModule = 'leaves';

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'is_half_day',
        'half_day_type',
        'reason',
        'status',
        'action_by',
        'action_at',
        'admin_remarks',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_days' => 'decimal:1',
            'is_half_day' => 'boolean',
            'action_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'approved' => [
                'label' => 'Approved',
                'class' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
            ],
            'pending' => [
                'label' => 'Pending Approval',
                'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            ],
            'rejected' => [
                'label' => 'Rejected',
                'class' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            ],
            default => [
                'label' => ucfirst($this->status),
                'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            ],
        };
    }

    /**
     * Synchronize approved leave dates into the attendances table.
     */
    public function syncToAttendance(): void
    {
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        $period = CarbonPeriod::create($start, $end);
        $typeName = $this->leaveType?->name ?? 'Leave';

        foreach ($period as $date) {
            $formattedDate = $date->toDateString();

            $attendance = Attendance::firstOrNew([
                'user_id' => $this->user_id,
                'date' => $formattedDate,
            ]);

            $attendance->status = $this->is_half_day ? 'half_day' : 'on_leave';
            $attendance->notes = ($this->is_half_day ? 'Half Day ' : '') . "Leave: {$typeName}" . ($this->reason ? " ({$this->reason})" : '');
            $attendance->marked_by = $this->action_by ?? auth()->id();
            $attendance->save();
        }
    }

    /**
     * Revert synced attendance records if leave is cancelled, rejected, or deleted.
     */
    public function revertAttendance(): void
    {
        $startDate = Carbon::parse($this->start_date)->toDateString();
        $endDate = Carbon::parse($this->end_date)->toDateString();

        Attendance::where('user_id', $this->user_id)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->whereIn('status', ['on_leave', 'half_day'])
            ->delete();
    }
}
