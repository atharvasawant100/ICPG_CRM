<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ContentMedia extends Model
{
    use HasFactory;

    protected $table = 'content_media';

    protected $fillable = [
        'content_post_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'media_type',
        'sort_order',
        'caption',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size'  => 'integer',
        'sort_order' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ContentPost::class, 'content_post_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image' || str_starts_with((string) $this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video' || str_starts_with((string) $this->mime_type, 'video/');
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

    public function url(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return Storage::disk('public')->url($this->file_path);
    }
}

