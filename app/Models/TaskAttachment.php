<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formattedFileSize(): string
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

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function fileExists(): bool
    {
        return Storage::disk('private')->exists($this->file_path)
            || Storage::disk('local')->exists($this->file_path)
            || Storage::disk('public')->exists($this->file_path);
    }
}

