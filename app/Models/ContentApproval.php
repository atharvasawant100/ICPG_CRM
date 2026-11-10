<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentApproval extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'content_post_id',
        'reviewer_id',
        'action', // approved, changes_requested
        'comment',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ContentPost::class, 'content_post_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function isApproved(): bool
    {
        return $this->action === 'approved';
    }

    public function isChangesRequested(): bool
    {
        return $this->action === 'changes_requested';
    }
}

