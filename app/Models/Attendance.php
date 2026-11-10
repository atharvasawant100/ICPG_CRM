<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use Auditable, HasFactory;

    protected $auditModule = 'attendance';

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'total_minutes',
        'status',
        'notes',
        'marked_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_minutes' => 'integer',
        ];
    }

    /**
     * Compute total working minutes from check_in and check_out.
     */
    public function computeTotalMinutes(): self
    {
        if ($this->check_in && $this->check_out) {
            $in = Carbon::parse($this->check_in);
            $out = Carbon::parse($this->check_out);

            if ($out->greaterThan($in)) {
                $this->total_minutes = $in->diffInMinutes($out);
            } else {
                $this->total_minutes = 0;
            }
        }

        return $this;
    }

    /**
     * Formatted string: e.g. "8 hrs 15 mins"
     */
    public function getWorkingHoursFormattedAttribute(): string
    {
        if (! $this->total_minutes) {
            return '-';
        }

        $hours = floor($this->total_minutes / 60);
        $minutes = $this->total_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Status styling configuration.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'present' => [
                'label' => 'Present',
                'class' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
            ],
            'late' => [
                'label' => 'Late',
                'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            ],
            'half_day' => [
                'label' => 'Half Day',
                'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            ],
            'absent' => [
                'label' => 'Absent',
                'class' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
            ],
            'on_leave' => [
                'label' => 'On Leave',
                'class' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
            ],
            'holiday' => [
                'label' => 'Holiday',
                'class' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-300',
            ],
            default => [
                'label' => ucfirst($this->status),
                'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            ],
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function taskWorkLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskWorkLog::class);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }
}
