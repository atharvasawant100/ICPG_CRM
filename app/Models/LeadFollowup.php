<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowup extends Model
{
    protected $fillable = [
        'lead_id',
        'user_id',
        'followup_date',
        'followup_type',
        'description',
        'status',
        'reminders_sent',
        'completed_at',
    ];

    protected $casts = [
        'followup_date' => 'datetime',
        'completed_at' => 'datetime',
        'reminders_sent' => 'array',
    ];

    /**
     * Get the lead that this follow-up belongs to
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user who created this follow-up
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if follow-up is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->followup_date < now();
    }

    /**
     * Mark follow-up as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
