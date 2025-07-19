<?php

declare(strict_types=1);

namespace Domain\Videos\Models;

use Database\Factories\VideoFactory;
use Domain\Groups\Concerns\InteractsWithGroups;
use Domain\Playlists\Concerns\InteractsWithPlaylists;
use Domain\Tags\Concerns\HasTags;
use Domain\Users\Concerns\InteractsWithUser;
use Domain\Users\Models\User;
use Domain\Videos\Collections\VideoCollection;
use Domain\Videos\Concerns\InteractsWithCache;
use Domain\Videos\Concerns\InteractsWithVod;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Video extends Model implements HasMedia
{
    use BroadcastsEvents;
    use HasFactory;
    use HasTags;
    use HasTranslations;
    use HasUlids;
    use InteractsWithCache;
    use InteractsWithGroups;
    use InteractsWithMedia;
    use InteractsWithPlaylists;
    use InteractsWithUser;
    use InteractsWithVod;
    use Searchable;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $with = [
        'tags',
    ];

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

    protected static function newFactory(): VideoFactory
    {
        return VideoFactory::new();
    }

    protected function casts(): array
    {
        return [
            'snapshot' => 'decimal:2',
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'released_at' => 'datetime',
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
                'video/x-matroska',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumbnail')
            ->performOnCollections('clips')
            ->withResponsiveImages()
            ->width(368)
            ->height(232)
            ->queued();
    }

    public function isFavoritedBy(?User $user = null): bool
    {
        if ($user === null) {
            return false;
        }

        return $user
            ->groups()
            ->favorites()
            ->whereRelation('videos', 'id', $this->getKey())
            ->exists();
    }

    public function isSavedBy(?User $user = null): bool
    {
        if ($user === null) {
            return false;
        }

        return $user
            ->groups()
            ->saves()
            ->whereRelation('videos', 'id', $this->getKey())
            ->exists();
    }

    public function isViewedBy(?User $user = null): bool
    {
        if ($user === null) {
            return false;
        }

        return $user
            ->groups()
            ->views()
            ->whereRelation('videos', 'id', $this->getKey())
            ->exists();
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(string $event): array
    {
        if ($event === 'deleted') {
            return [];
        }

        return [
            new PrivateChannel('users.'.$this->user->getRouteKey()),
            new PrivateChannel('videos.'.$this->getRouteKey()),
        ];
    }

    public function broadcastAs(string $event): ?string
    {
        return str($event)
            ->prepend('video.')
            ->trim('.')
            ->value();
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

    public function makeSearchableUsing(VideoCollection $models): VideoCollection
    {
        return $models->loadMissing('media', 'tags');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->getScoutKey(),
            'name' => (string) $this->name,
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];
    }

    protected function makeAllSearchableUsing(VideoQueryBuilder $query): VideoQueryBuilder
    {
        return $query->with(['media', 'tags']);
    }

    public function identifier(): Attribute
    {
        return Attribute::make(
            get: fn () => implode('-', array_filter([$this->season, $this->episode, $this->part]))
        )->shouldCache();
    }

    public function title(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(' - ', array_filter([$this->name, $this->part]))
        )->shouldCache();
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('clips', 'thumbnail')
        )->shouldCache();
    }

    public function srcset(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMedia('clips')?->getSrcset('thumbnail')
        )->shouldCache();
    }
}
