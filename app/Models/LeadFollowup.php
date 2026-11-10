<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use App\Rbac\HasDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowup extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFollowupFactory> */
    use Auditable, HasFactory, HasDataScope;

    /** Audit module label. */
    /** Audit module label. */
    protected $auditModule = 'followups';

    protected $fillable = [
        'lead_id',
        'user_id',
        'followup_date',
        'followup_type',
        'meeting_link',
        'agenda',
        'meeting_platform',
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
