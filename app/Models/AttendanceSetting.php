<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_start_time',
        'shift_end_time',
        'block_punch_in_after_shift_end',
        'late_mark_after',
        'half_day_login_after',
        'full_day_min_hours',
        'half_day_min_hours',
        'auto_half_day_on_short_hours',
        'enable_auto_punch_out',
        'auto_punch_out_time',
        'auto_punch_out_mode',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'block_punch_in_after_shift_end' => 'boolean',
            'full_day_min_hours' => 'decimal:2',
            'half_day_min_hours' => 'decimal:2',
            'auto_half_day_on_short_hours' => 'boolean',
            'enable_auto_punch_out' => 'boolean',
        ];
    }

    /**
     * Cache key for attendance settings singleton.
     */
    public const CACHE_KEY = 'attendance_global_settings';

    /**
     * Retrieve global attendance settings with fallback defaults.
     */
    public static function get(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::firstOrCreate([], [
                'shift_start_time' => '09:30',
                'shift_end_time' => '18:30',
                'block_punch_in_after_shift_end' => true,
                'late_mark_after' => '10:15',
                'half_day_login_after' => '13:00',
                'full_day_min_hours' => 8.00,
                'half_day_min_hours' => 4.00,
                'auto_half_day_on_short_hours' => true,
                'enable_auto_punch_out' => true,
                'auto_punch_out_time' => '19:00',
                'auto_punch_out_mode' => 'fixed_time',
            ]);
        });
    }

    /**
     * Clear cached settings.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }

    /**
     * Check whether employee punch-in is allowed at the specified time.
     */
    public function isPunchInAllowed(?Carbon $currentTime = null): bool
    {
        if (! ($this->block_punch_in_after_shift_end ?? true)) {
            return true;
        }

        $time = $currentTime ?: Carbon::now();
        $timeStr = $time->format('H:i');
        $shiftEndStr = substr($this->shift_end_time ?? '18:30', 0, 5);

        return $timeStr <= $shiftEndStr;
    }

    /**
     * Get human-readable formatted shift end time (e.g. 06:30 PM).
     */
    public function formattedShiftEndTime(): string
    {
        $time = substr($this->shift_end_time ?? '18:30', 0, 5);
        return Carbon::createFromFormat('H:i', $time)->format('h:i A');
    }

    /**
     * Evaluate punch-in status based on configured thresholds.
     */
    public function determinePunchInStatus(Carbon $currentTime, bool $isOnLeave = false): string
    {
        if ($isOnLeave) {
            return 'on_leave';
        }

        $timeStr = $currentTime->format('H:i');
        $halfDayThreshold = substr($this->half_day_login_after, 0, 5);
        $lateThreshold = substr($this->late_mark_after, 0, 5);

        if ($halfDayThreshold && $timeStr >= $halfDayThreshold) {
            return 'half_day';
        }

        if ($lateThreshold && $timeStr > $lateThreshold) {
            return 'late';
        }

        return 'present';
    }

    /**
     * User who last modified the settings.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
