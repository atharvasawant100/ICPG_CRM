<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskWorkLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'attendance_id',
        'worked_date',
        'status_before',
        'status_after',
        'notes',
    ];

    protected $casts = [
        'worked_date' => 'date',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
