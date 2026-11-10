<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'changed_by',
        'old_status',
        'new_status',
        'notes',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFromStatusAttribute(): ?string
    {
        return $this->old_status;
    }

    public function getToStatusAttribute(): string
    {
        return $this->new_status;
    }
}
