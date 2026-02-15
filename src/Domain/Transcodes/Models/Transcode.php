<?php

declare(strict_types=1);

namespace Domain\Transcodes\Models;

use Domain\Transcodes\Observers\TranscodeObserver;
use Domain\Transcodes\QueryBuilders\TranscodeQueryBuilder;
use Domain\Transcodes\States;
use Domain\Transcodes\States\TranscodeState;
use Domain\Shared\Casts\AsDateTime;
use Domain\Transcodes\Enums\TranscodeEncoder;
use Domain\Users\Concerns\InteractsWithUser;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Spatie\ModelStates\HasStates;

#[ObservedBy(TranscodeObserver::class)]
class Transcode extends Model
{
    use BroadcastsEvents;
    use HasFactory;
    use HasStates;
    use HasUlids;
    use InteractsWithUser;
    use Prunable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'video_id',
        'encoder',
        'state',
        'file_size',
        'error_message',
        'retry_count',
        'started_at',
        'transcoded_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'retry_count' => 'integer',
            'encoder' => TranscodeEncoder::class,
            'started_at' => AsDateTime::class,
            'transcoded_at' => AsDateTime::class,
            'created_at' => AsDateTime::class,
            'updated_at' => AsDateTime::class,
            'state' => TranscodeState::class,
        ];
    }

    public function newEloquentBuilder($query): TranscodeQueryBuilder
    {
        return new TranscodeQueryBuilder($query);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function prunable(): TranscodeQueryBuilder
    {
        return static::query()
            ->where('state', States\Failed::class)
            ->where('created_at', '<=', now()->subDays(7));
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function isPending(): bool
    {
        return $this->state->equals(States\Pending::class);
    }

    public function isProcessing(): bool
    {
        return $this->state->equals(States\Processing::class);
    }

    public function isCompleted(): bool
    {
        return $this->state->equals(States\Completed::class);
    }

    public function isFailed(): bool
    {
        return $this->state->equals(States\Failed::class);
    }

    public static function getDisk(): string
    {
        return Config::string('videos.transcode_disk', 'transcodes');
    }

    public function getPath(string $path = ''): string
    {
        return implode('/', array_filter([$this->ulid, $path]));
    }

    public function getFilename(): string
    {
        return pathinfo($this->media->file_name, PATHINFO_FILENAME).'.mp4';
    }

    public function getOutputPath(): string
    {
        return $this->getPath($this->getFilename());
    }

    public function getFilesystem(): FilesystemAdapter
    {
        return Storage::disk(static::getDisk());
    }

    public function getFileSize(): int
    {
        if ($this->file_size) {
            return $this->file_size;
        }

        if (! $this->isCompleted()) {
            return 0;
        }

        return $this->getFilesystem()->size($this->getOutputPath());
    }

    public function getHumanReadableFileSize(): string
    {
        return \Illuminate\Support\Number::fileSize($this->getFileSize());
    }

    public function markAsProcessing(): void
    {
        $this->state->transitionTo(States\Processing::class);

        $this->touch('started_at');
    }

    public function markAsCompleted(int $fileSize): void
    {
        $this->state->transitionTo(States\Completed::class);

        $this->updateOrFail([
            'file_size' => $fileSize,
            'transcoded_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->state->transitionTo(States\Failed::class);

        $this->updateOrFail([
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(string $event): array
    {
        return array_filter([$this, $this->media, $this->video]);
    }

    public function broadcastChannel(): string
    {
        return 'transcodes.'.$this->getRouteKey();
    }

    public function broadcastAs(string $event): string
    {
        return "transcode.{$event}";
    }

    public function broadcastWith(string $event): array
    {
        return ['id' => $this->getRouteKey()];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
