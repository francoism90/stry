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
    protected $translatable = [
        'name',
        'titles',
        'content',
        'summary',
    ];

    /**
     * @var array<int, string>
     */
    protected $with = [
        'tags',
    ];

    protected static function newFactory(): VideoFactory
    {
        return VideoFactory::new();
    }

    protected function casts(): array
    {
        return [
            'state' => VideoState::class,
            'snapshot' => 'decimal:2',
            'adult' => 'boolean',
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'released_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
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
                'video/mp4',
                'video/mp4v-es',
                'video/ogg',
                'video/quicktime',
                'video/webm',
                'video/x-m4v',
            ]);

        $this
            ->addMediaCollection('captions')
            ->useDisk('conversions')
            ->storeConversionsOnDisk('conversions')
            ->acceptsMimeTypes([
                'text/plain',
                'text/vtt',
            ]);

        $this
            ->addMediaCollection('previews')
            ->useDisk('conversions')
            ->storeConversionsOnDisk('conversions')
            ->singleFile()
            ->acceptsMimeTypes([
                'video/av1',
                'video/mp4',
                'video/mp4v-es',
                'video/ogg',
                'video/quicktime',
                'video/webm',
                'video/x-m4v',
            ]);

        $this
            ->addMediaCollection('thumbnail')
            ->useDisk('conversions')
            ->storeConversionsOnDisk('conversions')
            ->singleFile()
            ->withResponsiveImages()
            ->acceptsMimeTypes([
                'image/avif',
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(string $event): array
    {
        return [$this, $this->user];
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

    public function isValid(): bool
    {
        if (! $this->state->equals(Verified::class)) {
            return false;
        }

        return filled($this->expires_at) ? $this->expires_at->isFuture() : true;
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'name' => (string) $this->name,
            'title' => (string) $this->title,
            'content' => (string) $this->content,
            'summary' => (string) $this->summary,
            'duration' => (float) $this->duration,
            'captions' => (bool) $this->captions,
            'adult' => (bool) $this->adult,
            'tags' => (string) $this->tags->translated(),
            'synonyms' => (string) $this->tags->synonyms(),
            'tagged' => (array) $this->tags->modelKeys(),
            'state' => (string) $this->state,
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

    public function getCaptionCollection(): MediaCollection
    {
        return $this->getMedia('captions');
    }

    public function getStreams(): Collection
    {
        return $this
            ->getClipCollection()
            ->flatMap(fn (Media $media) => $media->getCustomProperty('streams', []));
    }

    public function hasCaptions(): bool
    {
        if ($this->getCaptionCollection()->isNotEmpty()) {
            return true;
        }

        return (bool) $this->getStreams()
            ->filter(fn (array $stream) => $stream['codec_type'] === 'subtitle' || data_get($stream, 'closed_captions', 0))
            ->isNotEmpty();
    }

    public function durationInSeconds(): float
    {
        return (float) $this->getStreams()->max('duration') ?: 0.0;
    }

    protected function identifier(): Attribute
    {
        return Attribute::make(
            get: fn () => implode('-', array_filter([$this->season, $this->episode]))
        )->shouldCache();
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(' - ', array_filter([$this->identifier, $this->name, $this->part]))
        )->shouldCache();
    }

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('thumbnail')
        )->shouldCache();
    }

    protected function srcset(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMedia('thumbnail')?->getSrcset()
        )->shouldCache();
    }

    protected function preview(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstPlaylist('preview')?->getUrl()
        )->shouldCache();
    }

    protected function captions(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hasCaptions()
        )->shouldCache();
    }

    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->durationInSeconds()
        )->shouldCache();
    }

    protected function timestamp(): Attribute
    {
        return Attribute::make(
            get: fn () => duration($this->duration)
        )->shouldCache();
    }

    protected function fileSize(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getClipCollection()->totalSizeInBytes(),
        )->shouldCache();
    }
}
