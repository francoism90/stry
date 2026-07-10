<?php

declare(strict_types=1);

namespace Domain\Videos\Models;

use Database\Factories\VideoFactory;
use Domain\Groups\Concerns\InteractsWithGroups;
use Domain\Playlists\Concerns\InteractsWithPlaylists;
use Domain\Shared\Casts\AsDateTime;
use Domain\Transcodes\Concerns\InteractsWithTranscodes;
use Domain\Users\Concerns\InteractsWithUser;
use Domain\Videos\Collections\VideoCollection;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Domain\Videos\States\Verified;
use Domain\Videos\States\VideoState;
use Foxws\ModelCache\Concerns\InteractsWithModelCache;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Number;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;
use Support\MediaLibrary\TemporaryUrls;

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
    use InteractsWithModelCache;
    use InteractsWithPlaylists;
    use InteractsWithTranscodes;
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
     * @var bool
     */
    public $registerMediaConversionsUsingModelInstance = true;

    protected function casts(): array
    {
        return [
            'snapshot' => 'decimal:2',
            'adult' => 'boolean',
            'expires_at' => AsDateTime::class,
            'published_at' => AsDateTime::class,
            'released_at' => AsDateTime::class,
            'created_at' => AsDateTime::class,
            'updated_at' => AsDateTime::class,
            'deleted_at' => AsDateTime::class,
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

    protected static function newFactory(): VideoFactory
    {
        return VideoFactory::new();
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
                'video/mpeg',
                'video/ogg',
                'video/quicktime',
                'video/webm',
                'video/x-m4v',
                'video/x-matroska',
                'video/x-mpeg',
                'video/x-msvideo',
            ]);

        $this
            ->addMediaCollection('captions')
            ->useDisk('media')
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

    public static function findFromUlid(Video|string $value): ?Video
    {
        if ($value instanceof Video) {
            return $value;
        }

        return Video::query()->firstWhere('ulid', $value);
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(string $event): array
    {
        return array_filter([$this, $this->user]);
    }

    public function broadcastChannel(): string
    {
        return 'videos.'.$this->getRouteKey();
    }

    public function broadcastAs(string $event): string
    {
        return "video.{$event}";
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

    public function isExpired(): bool
    {
        return filled($this->expires_at) && Carbon::parse($this->expires_at)->isPast();
    }

    public function isValid(): bool
    {
        return $this->state->equals(Verified::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'user_id' => (string) $this->user_id,
            'name' => (string) $this->name,
            'title' => (string) $this->title,
            'titles' => (string) $this->titles,
            'identifier' => (string) $this->identifier,
            'duration' => (float) $this->duration,
            'season' => (string) $this->season,
            'episode' => (string) $this->episode,
            'part' => (string) $this->part,
            'description' => (string) $this->summary,
            'released' => (string) $this->released,
            'clips' => (array) $this->clips,
            'filesize' => (int) $this->total_size,
            'captioned' => (bool) $this->captioned,
            'adult' => (bool) $this->adult,
            'tagged' => (array) $this->tags->modelKeys(),
            'tagged_count' => (int) $this->tags->count(),
            'synonyms' => (array) $this->tags->synonyms()->toArray(),
            'tags' => (array) $this->tags->translated()->toArray(),
            'expires_at' => (int) $this->expires_at?->getTimestamp(),
            'released_at' => (int) $this->released_at?->getTimestamp(),
            'published_at' => (int) $this->published_at?->getTimestamp(),
            'state' => (string) $this->state,
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
            'deleted_at' => (int) $this->deleted_at?->getTimestamp(),
        ];
    }

    public function makeSearchableUsing(VideoCollection $models): VideoCollection
    {
        return $models->loadMissing('media', 'tags');
    }

    protected function makeAllSearchableUsing(VideoQueryBuilder $query): VideoQueryBuilder
    {
        return $query->with(['media', 'tags']);
    }

    public static function getImportDisk(): string
    {
        return Config::string('videos.import_disk', 'import');
    }

    public static function getImportBatchSize(): int
    {
        return Config::integer('videos.import_batch_size', 10);
    }

    public static function shouldCreatePlaylist(): bool
    {
        return Config::boolean('videos.create_playlists', false);
    }

    public static function getCompletionThreshold(): float
    {
        return Config::float('videos.completion_threshold', 0.95);
    }

    public function getClips(): MediaCollection
    {
        return $this->getMedia('clips')->sortBy([
            ['custom_properties->streams->height', 'desc'],
            ['custom_properties->streams->width', 'desc'],
        ]);
    }

    public function getCaptions(): MediaCollection
    {
        return $this->getMedia('captions');
    }

    public function getStreams(): Collection
    {
        return $this
            ->getClips()
            ->flatMap(fn (Media $media) => $media->getCustomProperty('streams', []));
    }

    public function getVideoStreams(): Collection
    {
        return $this->getStreams()
            ->filter(fn (array $stream) => $stream['codec_type'] === 'video');
    }

    public function getAudioStreams(): Collection
    {
        return $this->getStreams()
            ->filter(fn (array $stream) => $stream['codec_type'] === 'audio');
    }

    public function getCaptionStreams(): Collection
    {
        return $this->getStreams()
            ->filter(fn (array $stream) => $stream['codec_type'] === 'subtitle' || data_get($stream, 'closed_captions'));
    }

    public function hasCaptions(): bool
    {
        if ($this->getCaptions()->isNotEmpty()) {
            return true;
        }

        return $this->getCaptionStreams()->isNotEmpty();
    }

    protected function getThumbMedia(): ?Media
    {
        $media = $this->getClips()->first();

        if (! $media) {
            return null;
        }

        $media->setRelation('model', $this);

        return $media;
    }

    public function thumbnailUrl(): ?string
    {
        $media = $this->getThumbMedia();

        if (! $media) {
            return null;
        }

        return rescue(fn () => TemporaryUrls::make($media)->getUrl('thumb'));
    }

    public function thumbnailSrcset(): ?string
    {
        $media = $this->getThumbMedia();

        if (! $media) {
            return null;
        }

        return rescue(fn () => TemporaryUrls::make($media)->getSrcset('thumb'));
    }

    public function durationInSeconds(): float
    {
        return (float) $this->getStreams()->max('duration') ?? 0.0;
    }

    protected function identifier(): Attribute
    {
        return Attribute::make(
            get: fn (): string => implode('', array_filter([$this->season, $this->episode])),
        )->shouldCache();
    }

    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn (): string => implode(' · ', array_filter([$this->identifier, $this->name])),
        )->shouldCache();
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn (): string => implode(' - ', array_filter([$this->label, $this->part])),
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
            get: fn (): ?string => $this->thumbnailUrl(),
        )->shouldCache();
    }

    protected function thumbSrcset(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->thumbnailSrcset(),
        )->shouldCache();
    }

    protected function clips(): Attribute
    {
        return Attribute::make(
            get: fn (): array => $this->getClips()->pluck('file_name')->toArray(),
        )->shouldCache();
    }

    protected function totalSize(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->getClips()->totalSizeInBytes(),
        )->shouldCache();
    }

    protected function filesize(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Number::fileSize((int) $this->totalSize),
        )->shouldCache();
    }

    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->durationInSeconds(),
        )->shouldCache();
    }

    protected function timestamp(): Attribute
    {
        return Attribute::make(
            get: fn (): string => duration($this->durationInSeconds()),
        )->shouldCache();
    }

    protected function released(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Carbon::parse($this->released_at ?: $this->created_at)->toDateString(),
        )->shouldCache();
    }

    protected function captioned(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->hasCaptions(),
        )->shouldCache();
    }
}
