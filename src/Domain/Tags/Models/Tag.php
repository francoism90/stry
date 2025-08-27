<?php

declare(strict_types=1);

namespace Domain\Tags\Models;

use Database\Factories\TagFactory;
use Domain\Media\Concerns\InteractsWithMedia;
use Domain\Relates\Concerns\InteractsWithRelated;
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
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
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

    public function videos(): MorphToMany
    {
        return $this->morphedByMany(Video::class, 'taggable');
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

    public function broadcastChannelRoute(): string
    {
        return 'tags.{tag}';
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
            'id' => (int) $this->getScoutKey(),
            'name' => (string) $this->name,
            'description' => (string) $this->description,
            'category' => (string) $this->category,
            'type' => (string) $this->type?->value,
            'adult' => (bool) $this->adult,
            'synonyms' => (string) $this->synonyms,
            'order' => (int) $this->order_column,
            'videos' => (int) $this->videos()->count(),
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];
    }

    protected function category(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type?->label(),
        )->shouldCache();
    }

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->videos()->first()?->thumbnail,
        )->shouldCache();
    }

    protected function srcset(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->videos()->first()?->srcset,
        )->shouldCache();
    }

    protected function synonyms(): Attribute
    {
        return Attribute::make(
            get: fn () => TagCollection::make($this->relates)->synonyms(),
        )->shouldCache();
    }
}
