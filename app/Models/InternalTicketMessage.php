<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_ticket_id',
        'user_id',
        'message',
        'is_admin_note',
    ];

    protected $casts = [
        'is_admin_note' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(InternalTicket::class, 'internal_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

