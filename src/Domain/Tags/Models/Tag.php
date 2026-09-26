<?php

declare(strict_types=1);

namespace Domain\Tags\Models;

use ArrayAccess;
use Database\Factories\TagFactory;
use Domain\Media\Concerns\InteractsWithMedia;
use Domain\Relates\Concerns\InteractsWithRelated;
use Domain\Shared\Casts\AsDateTime;
use Domain\Shared\Concerns\BroadcastsModelEvents;
use Domain\Shared\Concerns\HasUlidRouteKey;
use Domain\Shared\Concerns\InteractsWithCache;
use Domain\Tags\Collections\TagCollection;
use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Domain\Users\Concerns\InteractsWithUser;
use Domain\Videos\Models\Video;
use Foxws\ScoutRelations\Concerns\HasSearchableRelations;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\Tag as BaseTag;

#[CollectedBy(TagCollection::class)]
#[UseEloquentBuilder(TagQueryBuilder::class)]
class Tag extends BaseTag implements HasMedia
{
    use BroadcastsModelEvents;
    use HasFactory;
    use HasSearchableRelations;
    use HasUlidRouteKey;
    use InteractsWithCache;
    use InteractsWithMedia;
    use InteractsWithRelated;
    use InteractsWithUser;
    use Searchable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'adult',
    ];

    /**
     * @var list<string>
     */
    public array $translatable = [
        'name',
        'slug',
        'description',
    ];

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }

    protected function casts(): array
    {
        return [
            'type' => TagType::class,
            'adult' => 'boolean',
            'created_at' => AsDateTime::class,
            'updated_at' => AsDateTime::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('avatar')
            ->useDisk('conversions')
            ->storeConversionsOnDisk('conversions')
            ->singleFile()
            ->withResponsiveImages()
            ->acceptsMimeTypes([
                'image/avif',
                'image/gif',
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/svg+xml',
                'image/tiff',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->fit(Fit::Stretch, 1280, 720)
            ->sharpen(10);
    }

    /**
     * @return MorphToMany<Video, $this>
     */
    public function videos(): MorphToMany
    {
        return $this->morphedByMany(Video::class, 'taggable');
    }

    /**
     * @return Collection<int, string>
     */
    public static function resolveTagIds(Tag|ArrayAccess|array|string $values): Collection
    {
        if ($values instanceof Tag) {
            return Collection::make([$values->getKey()]);
        }

        return Collection::wrap($values)
            ->map(fn (Tag|string $tag) => static::findFromUlid($tag)?->getKey())
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * @return array<int, Channel|Model>
     */
    public function broadcastOn(string $event): array
    {
        return [$this];
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'name' => (string) $this->name,
            'description' => (string) $this->description,
            'category' => (string) $this->category,
            'type' => (string) $this->type?->value,
            'adult' => (bool) $this->adult,
            'synonyms' => (array) $this->synonyms->toArray(),
            'translated' => (array) $this->translated->toArray(),
            'order' => (int) $this->order_column,
            'videos' => (int) $this->videos_count,
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];
    }

    public function searchableRelations(): array
    {
        return ['videos'];
    }

    public function makeSearchableUsing(TagCollection $models): TagCollection
    {
        return $models
            ->loadMissing('related')
            ->loadCount('videos');
    }

    protected function makeAllSearchableUsing(TagQueryBuilder $query): TagQueryBuilder
    {
        return $query
            ->with('related')
            ->withCount('videos');
    }

    /**
     * @return Attribute<string, never>
     */
    protected function summary(): Attribute
    {
        return Attribute::make(
            get: fn () => markdown($this->description ?? ''),
        )->shouldCache();
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function category(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type?->label(),
        )->shouldCache();
    }

    /**
     * @return Attribute<Collection<int, mixed>, never>
     */
    protected function synonyms(): Attribute
    {
        return Attribute::make(
            get: fn () => TagCollection::make($this->getRelates())->synonyms(),
        )->shouldCache();
    }

    /**
     * @return Attribute<Collection<int, mixed>, never>
     */
    protected function translated(): Attribute
    {
        return Attribute::make(
            get: fn () => TagCollection::make([$this])->translated(),
        )->shouldCache();
    }
}
