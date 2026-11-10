<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalTicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_ticket_id',
        'user_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(InternalTicket::class, 'internal_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formattedSize(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}

