<?php

declare(strict_types=1);

namespace Domain\Media\Models;

use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'media_id',
        'codec',
        'preset',
        'state',
        'progress',
        'error_message',
        'retry_count',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'retry_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function isPending(): bool
    {
        return $this->state === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->state === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->state === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->state === 'failed';
    }
}
