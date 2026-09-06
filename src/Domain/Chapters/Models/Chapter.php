<?php

declare(strict_types=1);

namespace Domain\Chapters\Models;

use Database\Factories\ChapterFactory;
use Domain\Chapters\Enums\ChapterType;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
{
    use HasFactory;
    use HasUlids;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'video_id',
        'type',
        'label',
        'start_time',
        'end_time',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'type' => ChapterType::class,
            'start_time' => 'decimal:2',
            'end_time' => 'decimal:2',
            'sort' => 'integer',
        ];
    }

    protected static function newFactory(): ChapterFactory
    {
        return ChapterFactory::new();
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
