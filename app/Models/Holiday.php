<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use Auditable, HasFactory;

    protected $auditModule = 'holidays';

    protected $fillable = [
        'name',
        'date',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a given date is an official holiday.
     */
    public static function isHoliday(string|Carbon $date): bool
    {
        $d = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return static::whereDate('date', $d)->exists();
    }

    /**
     * Get holiday for a given date, if one exists.
     */
    public static function forDate(string|Carbon $date): ?self
    {
        $d = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return static::whereDate('date', $d)->first();
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('date', '>=', now()->toDateString())->orderBy('date');
    }

    public function scopePast($query)
    {
        return $query->whereDate('date', '<', now()->toDateString())->orderByDesc('date');
    }
}
