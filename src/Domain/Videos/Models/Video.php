<?php

declare(strict_types=1);

namespace Domain\Videos\Models;

use Database\Factories\VideoFactory;
use Domain\Chapters\Concerns\InteractsWithChapters;
use Domain\Groups\Concerns\InteractsWithGroups;
use Domain\Media\Models\Media;
use Domain\Playlists\Concerns\InteractsWithPlaylists;
use Domain\Shared\Casts\AsDate;
use Domain\Shared\Casts\AsDateTime;
use Domain\Shared\Concerns\BroadcastsModelEvents;
use Domain\Shared\Concerns\HasUlidRouteKey;
use Domain\Tags\Collections\TagCollection;
use Domain\Transcodes\Concerns\InteractsWithTranscodes;
use Domain\Users\Concerns\InteractsWithUser;
use Domain\Videos\Collections\VideoCollection;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Domain\Videos\States\Verified;
use Domain\Videos\States\VideoState;
use Foxws\ModelCache\Concerns\InteractsWithModelCache;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;
use Support\MediaLibrary\TemporaryUrls;

/**
 * @property-read TagCollection $tags
 */
#[CollectedBy(VideoCollection::class)]
#[UseEloquentBuilder(VideoQueryBuilder::class)]
class Video extends Model implements HasMedia
{
    use BroadcastsModelEvents;
    use HasFactory;
    use HasStates;
    use HasTags;
    use HasTranslations;
    use HasUlidRouteKey;
    use InteractsWithChapters;
    use InteractsWithGroups;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    use InteractsWithModelCache;
    use InteractsWithPlaylists;
    use InteractsWithTranscodes;
    use InteractsWithUser;
    use Searchable;
    use SoftDeletes;

    /**
     * @var list<string>
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
     * @var list<string>
     */
    protected $hidden = [
        'user_id',
    ];

    /**
     * @var list<string>
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
            'released_at' => AsDate::class,
            'created_at' => AsDateTime::class,
            'updated_at' => AsDateTime::class,
            'deleted_at' => AsDateTime::class,
            'state' => VideoState::class,
        ];
    }

    protected static function newFactory(): VideoFactory
    {
        return VideoFactory::new();
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

        $this
            ->addMediaCollection('storyboards')
            ->useDisk('conversions')
            ->storeConversionsOnDisk('conversions')
            ->acceptsMimeTypes([
                'application/octet-stream',
                'application/x-webvtt',
                'image/jpeg',
                'text/plain',
                'text/vtt',
            ]);

        $this
            ->addMediaCollection('chapters')
            ->useDisk('conversions')
            ->storeConversionsOnDisk('conversions')
            ->singleFile()
            ->acceptsMimeTypes([
                'application/octet-stream',
                'application/x-webvtt',
                'text/plain',
                'text/vtt',
            ]);
    }

    public function registerMediaConversions(?BaseMedia $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->performOnCollections('clips')
            ->withResponsiveImages()
            ->extractVideoFrameAtSecond((float) $this->snapshot ?: round($this->duration / 2))
            ->fit(Fit::Stretch, 1280, 720)
            ->sharpen(10)
            ->format('avif');
    }

    /**
     * @return array<int, Channel|Model>
     */
    public function broadcastOn(string $event): array
    {
        return array_filter([$this, $this->user, new PrivateChannel('library')]);
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

    /**
     * @return MediaCollection<int, Media>
     */
    public function getClips(): MediaCollection
    {
        return $this->getMedia('clips')->sortByDesc(function (Media $media) {
            $stream = $media->getVideoStream();

            return [
                $stream['height'] ?? 0,
                $stream['width'] ?? 0,
                $stream['bit_rate'] ?? 0,
            ];
        });
    }

    /**
     * @return MediaCollection<int, Media>
     */
    public function getCaptions(): MediaCollection
    {
        return $this->getMedia('captions');
    }

    public function getStoryboardImage(): ?BaseMedia
    {
        return $this->getMedia('storyboards')->firstWhere('mime_type', 'image/jpeg');
    }

    public function getStoryboardVtt(): ?BaseMedia
    {
        return $this->getMedia('storyboards')->first(fn (BaseMedia $media) => $media->mime_type !== 'image/jpeg');
    }

    public function getChaptersVtt(): ?BaseMedia
    {
        return $this->getMedia('chapters')->first();
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

    protected function getThumbMedia(): ?BaseMedia
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
        return $this->temporaryMediaUrl($this->getThumbMedia(), 'thumb');
    }

    public function thumbnailSrcset(): ?string
    {
        $media = $this->getThumbMedia();

        if (! $media) {
            return null;
        }

        return rescue(fn () => TemporaryUrls::make($media)->getSrcset('thumb'));
    }

    public function storyboardImageUrl(): ?string
    {
        return $this->temporaryMediaUrl($this->getStoryboardImage());
    }

    public function storyboardVttUrl(): ?string
    {
        return $this->temporaryMediaUrl($this->getStoryboardVtt());
    }

    public function chaptersVttUrl(): ?string
    {
        return $this->temporaryMediaUrl($this->getChaptersVtt());
    }

    protected function temporaryMediaUrl(?BaseMedia $media, string $conversion = ''): ?string
    {
        if (! $media) {
            return null;
        }

        return rescue(fn () => TemporaryUrls::make($media)->getUrl($conversion));
    }

    public function durationInSeconds(): float
    {
        return (float) $this->getStreams()->max('duration');
    }

    /**
     * @return Attribute<string, never>
     */
    protected function identifier(): Attribute
    {
        return Attribute::make(
            get: fn (): string => implode('', array_filter([$this->season, $this->episode])),
        )->shouldCache();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn (): string => implode(' · ', array_filter([$this->identifier, $this->name])),
        )->shouldCache();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn (): string => implode(' - ', array_filter([$this->label, $this->part])),
        )->shouldCache();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (): string => markdown($this->summary ?? ''),
        )->shouldCache();
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function thumb(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->thumbnailUrl(),
        )->shouldCache();
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function thumbSrcset(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->thumbnailSrcset(),
        )->shouldCache();
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function storyboardImage(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->storyboardImageUrl(),
        )->shouldCache();
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function storyboardVtt(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->storyboardVttUrl(),
        )->shouldCache();
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function chaptersVtt(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->chaptersVttUrl(),
        )->shouldCache();
    }

    /**
     * @return Attribute<array, never>
     */
    protected function clips(): Attribute
    {
        return Attribute::make(
            get: fn (): array => $this->getClips()->pluck('file_name')->toArray(),
        )->shouldCache();
    }

    /**
     * @return Attribute<int, never>
     */
    protected function totalSize(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->getClips()->totalSizeInBytes(),
        )->shouldCache();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function filesize(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Number::fileSize((int) $this->totalSize),
        )->shouldCache();
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function codec(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->getClips()->first()?->codec,
        )->shouldCache();
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function resolution(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->getClips()->first()?->resolution,
        )->shouldCache();
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function bitrate(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->getClips()->first()?->bitrate,
        )->shouldCache();
    }

    /**
     * @return Attribute<float, never>
     */
    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->durationInSeconds(),
        )->shouldCache();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function timestamp(): Attribute
    {
        return Attribute::make(
            get: fn (): string => duration($this->duration),
        )->shouldCache();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function released(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Carbon::parse($this->released_at ?: $this->created_at)->toDateString(),
        )->shouldCache();
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function captioned(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->hasCaptions(),
        )->shouldCache();
    }
}
