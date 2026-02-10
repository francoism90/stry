<?php

declare(strict_types=1);

namespace Domain\Tags\Models;

use ArrayAccess;
use Database\Factories\TagFactory;
use Domain\Media\Concerns\InteractsWithMedia;
use Domain\Relates\Concerns\InteractsWithRelated;
use Domain\Shared\Casts\AsDateTime;
use Domain\Tags\Collections\TagCollection;
use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Domain\Users\Concerns\InteractsWithUser;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\Tag as BaseTag;

class Tag extends BaseTag implements HasMedia
{
    use BroadcastsEvents;
    use HasFactory;
    use HasUlids;
    use InteractsWithMedia;
    use InteractsWithRelated;
    use InteractsWithUser;
    use Searchable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'adult',
    ];

    /**
     * @var array<int, string>
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

    public function newEloquentBuilder($query): TagQueryBuilder
    {
        return new TagQueryBuilder($query);
    }

    public function newCollection(array $models = []): TagCollection
    {
        return new TagCollection($models);
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

    public static function findFromUlid(Tag|string $value): ?Tag
    {
        if ($value instanceof Tag) {
            return $value;
        }

        return Tag::query()->firstWhere('ulid', $value);
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(string $event): array
    {
        return [$this];
    }

    public function broadcastChannel(): string
    {
        return 'tags.'.$this->getRouteKey();
    }

    public function broadcastAs(string $event): string
    {
        return "tag.{$event}";
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

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'name' => (string) $this->name,
            'description' => (string) $this->description,
            'category' => (string) $this->category,
            'type' => (string) $this->type?->value ?? '',
            'adult' => (bool) $this->adult,
            'synonyms' => (string) $this->synonyms,
            'order' => (int) $this->order_column,
            'videos' => (int) $this->videos()->count(),
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];
    }

    protected function summary(): Attribute
    {
        return Attribute::make(
            get: fn () => markdown($this->description ?? ''),
        )->shouldCache();
    }

    protected function category(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type?->label(),
        )->shouldCache();
    }

    protected function synonyms(): Attribute
    {
        return Attribute::make(
            get: fn () => TagCollection::make($this->getRelates())->synonyms(),
        )->shouldCache();
    }
}
