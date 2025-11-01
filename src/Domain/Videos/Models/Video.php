<?php

declare(strict_types=1);

namespace Domain\Videos\Models;

use Database\Factories\VideoFactory;
use Domain\Groups\Concerns\InteractsWithGroups;
use Domain\Playlists\Concerns\InteractsWithPlaylists;
use Domain\Users\Concerns\InteractsWithUser;
use Domain\Videos\Collections\VideoCollection;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Domain\Videos\States\Verified;
use Domain\Videos\States\VideoState;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;

class Video extends Model implements HasMedia
{
    use BroadcastsEvents;
    use HasFactory;
    use HasStates;
    use HasTags;
    use HasTranslations;
    use HasUlids;
    use InteractsWithGroups;
    use InteractsWithMedia;
    use InteractsWithPlaylists;
    use InteractsWithUser;
    use Searchable;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'titles',
        'content',
        'summary',
        'season',
        'episode',
        'part',
        'adult',
        'snapshot',
        'state',
        'expires_at',
        'published_at',
        'released_at',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',
    ];

    /**
     * @var array<int, string>
     */
    protected $with = [
        'tags',
    ];

    /**
     * @var array<int, string>
     */
    protected $translatable = [
        'name',
        'titles',
        'content',
        'summary',
    ];

    /**
     * @var bool
     */
    public $registerMediaConversionsUsingModelInstance = true;

    protected static function newFactory(): VideoFactory
    {
        return VideoFactory::new();
    }

    protected function casts(): array
    {
        return [
            'snapshot' => 'decimal:2',
            'adult' => 'boolean',
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'released_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'state' => VideoState::class,
        ];
    }

    public function newEloquentBuilder($query): VideoQueryBuilder
    {
        return new VideoQueryBuilder($query);
    }

    public function newCollection(array $models = []): VideoCollection
    {
        return new VideoCollection($models);
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('clips')
            ->useDisk('media')
            ->storeConversionsOnDisk('conversions')
            ->acceptsMimeTypes([
                'video/av1',
                'video/avi',
                'video/mkv',
                'video/mp4',
                'video/mp4v-es',
                'video/mpeg',
                'video/ogg',
                'video/quicktime',
                'video/webm',
                'video/x-flv',
                'video/x-m4v',
                'video/x-matroska',
                'video/x-mpeg',
                'video/x-ms-asf',
                'video/x-ms-wmv',
                'video/x-msvideo',
            ]);

        $this
            ->addMediaCollection('captions')
            ->useDisk('conversions')
            ->storeConversionsOnDisk('conversions')
            ->acceptsMimeTypes([
                'application/octet-stream',
                'application/x-subrip',
                'application/x-subtitle',
                'application/x-subviewer',
                'application/x-webvtt',
                'text/plain',
                'text/srt',
                'text/vtt',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->performOnCollections('clips')
            ->fit(Fit::Stretch, 1280, 720)
            ->sharpen(10)
            ->format('avif')
            ->withResponsiveImages()
            ->extractVideoFrameAtSecond((float) $this->snapshot ?: round($this->duration / 2));
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(string $event): array
    {
        return array_filter([$this, $this->user]);
    }

    public function broadcastChannel(): string
    {
        return 'videos.'.$this->getRouteKey();
    }

    public function broadcastChannelRoute(): string
    {
        return 'videos.{video}';
    }

    public function broadcastAs(string $event): string
    {
        return "video.{$event}";
    }

    public function broadcastWith(string $event): array
    {
        return ['id' => $this->getRouteKey()];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }

    public function broadcastAfterCommit(): bool
    {
        return true;
    }

    public function isExpired(): bool
    {
        return filled($this->expires_at) && $this->expires_at->isPast();
    }

    public function isProcessed(): bool
    {
        return $this->state->equals(Verified::class);
    }

    public function makeSearchableUsing(VideoCollection $models): VideoCollection
    {
        return $models->loadMissing($this->with);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'name' => (string) $this->name,
            'title' => (string) $this->title,
            'description' => (string) $this->description,
            'duration' => (float) $this->duration,
            'captioned' => (bool) $this->captioned,
            'adult' => (bool) $this->adult,
            'tags' => (string) $this->tags->translated(),
            'synonyms' => (string) $this->tags->synonyms(),
            'tagged' => (array) $this->tags->modelKeys(),
            'state' => (string) $this->state,
            'published_at' => (int) $this->published_at?->getTimestamp(),
            'released_at' => (int) $this->released_at?->getTimestamp(),
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];
    }

    public function getClipCollection(): MediaCollection
    {
        return $this->getMedia('clips')->sortBy([
            ['custom_properties->streams->bit_rate', 'desc'],
            ['custom_properties->streams->width', 'desc'],
            ['custom_properties->streams->height', 'desc'],
        ]);
    }

    public function getStreams(): Collection
    {
        return $this
            ->getClipCollection()
            ->flatMap(fn (Media $media) => $media->getCustomProperty('streams', []));
    }

    public function getCaptions(): MediaCollection
    {
        return $this->getMedia('captions');
    }

    public function hasCaptions(): bool
    {
        if ($this->getCaptions()->isNotEmpty()) {
            return true;
        }

        return (bool) $this->getStreams()
            ->filter(fn (array $stream) => $stream['codec_type'] === 'subtitle' || data_get($stream, 'closed_captions'))
            ->isNotEmpty();
    }

    public function durationInSeconds(): float
    {
        return (float) $this->getStreams()->max('duration') ?: 0.0;
    }

    protected function identifier(): Attribute
    {
        return Attribute::make(
            get: fn () => implode('', array_filter([$this->season, $this->episode]))
        )->shouldCache();
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(' - ', array_filter([$this->identifier, $this->name, $this->part]))
        )->shouldCache();
    }

    protected function released(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->released_at ?? $this->published_at ?? $this->created_at
        )->shouldCache();
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (): string => markdown($this->summary ?? ''),
        )->shouldCache();
    }

    protected function thumb(): Attribute
    {
        return Attribute::make(
            get: fn () => rescue(fn (): ?string => $this->getFirstTemporaryUrl(now()->addDays(3), 'clips', 'thumb'), report: false)
        )->shouldCache();
    }

    protected function captioned(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->hasCaptions()
        )->shouldCache();
    }

    protected function captions(): Attribute
    {
        return Attribute::make(
            get: fn (): MediaCollection => $this->getCaptions()
        )->shouldCache();
    }

    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->durationInSeconds()
        )->shouldCache();
    }

    protected function timestamp(): Attribute
    {
        return Attribute::make(
            get: fn (): string => duration($this->durationInSeconds())
        )->shouldCache();
    }

    protected function fileSize(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->getClipCollection()->totalSizeInBytes(),
        )->shouldCache();
    }
}
