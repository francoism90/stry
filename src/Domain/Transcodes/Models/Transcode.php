<?php

declare(strict_types=1);

namespace Domain\Transcodes\Models;

use Database\Factories\TranscodeFactory;
use Domain\Shared\Casts\AsDateTime;
use Domain\Transcodes\Collections\TranscodeCollection;
use Domain\Transcodes\Enums\TranscodeEncoder;
use Domain\Transcodes\Observers\TranscodeObserver;
use Domain\Transcodes\QueryBuilders\TranscodeQueryBuilder;
use Domain\Transcodes\States;
use Domain\Transcodes\States\TranscodeState;
use Domain\Users\Concerns\InteractsWithUser;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Laravel\Scout\Searchable;
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
    use Searchable;

    protected static function newFactory(): TranscodeFactory
    {
        return TranscodeFactory::new();
    }

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'transcodable_type',
        'transcodable_id',
        'disk',
        'file_name',
        'file_size',
        'encoder',
        'state',
        'error_message',
        'retry_count',
        'started_at',
        'transcoded_at',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',
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

    public function newCollection(array $models = []): TranscodeCollection
    {
        return new TranscodeCollection($models);
    }

    public function transcodable(): MorphTo
    {
        return $this->morphTo();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'user_id' => (string) $this->user_id,
            'transcodable_type' => (string) $this->transcodable_type,
            'transcodable_id' => (string) $this->transcodable_id,
            'name' => (string) ($this->transcodable?->label ?? $this->file_name ?? ''),
            'file_name' => (string) $this->file_name,
            'encoder' => (string) $this->encoder?->value,
            'file_size' => (int) $this->file_size,
            'state' => (string) $this->state,
            'started_at' => (int) $this->started_at?->getTimestamp(),
            'transcoded_at' => (int) $this->transcoded_at?->getTimestamp(),
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];
    }

    public function makeSearchableUsing(TranscodeCollection $models): TranscodeCollection
    {
        return $models->loadMissing('transcodable');
    }

    protected function makeAllSearchableUsing(TranscodeQueryBuilder $query): TranscodeQueryBuilder
    {
        return $query->with('transcodable');
    }

    public function prunable(): TranscodeQueryBuilder
    {
        return static::query()->prunable();
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public static function findFromUlid(Transcode|string $value): ?Transcode
    {
        if ($value instanceof Transcode) {
            return $value;
        }

        return Transcode::query()->firstWhere('ulid', $value);
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(string $event): array
    {
        return array_filter([$this, $this->transcodable]);
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

    public function broadcastAfterCommit(): bool
    {
        return true;
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
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

    public function isImported(): bool
    {
        return $this->state->equals(States\Imported::class);
    }

    public function getDisk(): string
    {
        return $this->disk ?? static::getDestinationDisk();
    }

    public function getPath(string $path = ''): string
    {
        return implode('/', array_filter([$this->ulid, $path]));
    }

    public function getOutputPath(): string
    {
        return $this->getPath($this->file_name);
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

    public function markAsProcessing(): void
    {
        $this->state->transitionTo(States\Processing::class);

        $this->touch('started_at');
    }

    public function markAsCompleted(): void
    {
        $this->state->transitionTo(States\Completed::class);

        $this->updateOrFail([
            'file_size' => $this->getFileSize(),
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

    public function markAsImported(): void
    {
        $this->state->transitionTo(States\Imported::class);
    }

    public static function getDestinationDisk(): string
    {
        return Config::string('videos.transcode_disk', 'cache');
    }

    protected function humanFileSize(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Number::fileSize($this->getFileSize()),
        )->shouldCache();
    }
}
