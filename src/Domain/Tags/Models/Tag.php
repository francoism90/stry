<?php

declare(strict_types=1);

namespace Domain\Tags\Models;

use Database\Factories\TagFactory;
use Domain\Media\Concerns\InteractsWithMedia;
use Domain\Relates\Concerns\HasRelates;
use Domain\Tags\Collections\TagCollection;
use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Domain\Tags\Scopes\OrderedScope;
use Domain\Users\Concerns\InteractsWithUser;
use Domain\Videos\Models\Video;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Tags\Tag as BaseTag;

#[ScopedBy(OrderedScope::class)]
class Tag extends BaseTag implements HasMedia
{
    use BroadcastsEvents;
    use HasFactory;
    use HasRelates;
    use HasUlids;
    use InteractsWithMedia;
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

    public function videos(): MorphToMany
    {
        return $this->morphedByMany(Video::class, 'taggable');
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

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(string $event): array
    {
        if ($event === 'deleted') {
            return [];
        }

        return [
            new PrivateChannel('tags.'.$this->getRouteKey()),
        ];
    }

    public function broadcastAs(string $event): ?string
    {
        return str($event)
            ->prepend('tag.')
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

    public function makeSearchableUsing(TagCollection $models): TagCollection
    {
        return $models->loadMissing('relatables');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->getScoutKey(),
            'name' => (string) $this->name,
            'description' => (string) $this->description,
            'type' => (string) $this->type?->value,
            'adult' => (bool) $this->adult,
            'synonyms' => (string) $this->synonyms,
            'related' => (array) $this->relatables->modelKeys(),
            'order' => (int) $this->order_column,
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];
    }

    protected function makeAllSearchableUsing(TagQueryBuilder $query): TagQueryBuilder
    {
        return $query->with(['relatables']);
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->videos()->first()?->thumbnail,
        )->shouldCache();
    }

    public function synonyms(): Attribute
    {
        return Attribute::make(
            get: fn () => TagCollection::make($this->related)->translated(),
        )->shouldCache();
    }
}
