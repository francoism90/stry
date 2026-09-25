<?php

declare(strict_types=1);

namespace Domain\Groups\Models;

use Database\Factories\GroupFactory;
use Domain\Groups\Collections\GroupCollection;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\QueryBuilders\GroupQueryBuilder;
use Domain\Groups\States\GroupState;
use Domain\Media\Concerns\InteractsWithMedia;
use Domain\Shared\Casts\AsDateTime;
use Domain\Shared\Concerns\BroadcastsModelEvents;
use Domain\Shared\Concerns\HasUlidRouteKey;
use Domain\Users\Concerns\InteractsWithUser;
use Domain\Videos\Models\Video;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;
use Spatie\Translatable\HasTranslations;

#[CollectedBy(GroupCollection::class)]
#[UseEloquentBuilder(GroupQueryBuilder::class)]
class Group extends Model implements HasMedia, Sortable
{
    use BroadcastsModelEvents;
    use HasFactory;
    use HasStates;
    use HasTranslations;
    use HasUlidRouteKey;
    use InteractsWithMedia;
    use InteractsWithUser;
    use Notifiable;
    use Prunable;
    use Searchable;
    use SoftDeletes;
    use SortableTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'content',
        'type',
        'state',
        'options',
        'order_column',
        'expires_at',
        'published_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'user_id',
    ];

    /**
     * @var array<string, mixed>
     */
    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

    /**
     * @var list<string>
     */
    public array $translatable = [
        'content',
    ];

    protected function casts(): array
    {
        return [
            'state' => GroupState::class,
            'type' => GroupType::class,
            'options' => AsArrayObject::class,
            'expires_at' => AsDateTime::class,
            'published_at' => AsDateTime::class,
            'deleted_at' => AsDateTime::class,
        ];
    }

    protected static function newFactory(): GroupFactory
    {
        return GroupFactory::new();
    }

    public function groupables(): HasMany
    {
        return $this->hasMany(Groupable::class, 'group_id');
    }

    public function videos(): MorphToMany
    {
        return $this
            ->morphedByMany(Video::class, 'groupable')
            ->using(Groupable::class)
            ->withTimestamps();
    }

    public function getGroupable(Model $model): ?Groupable
    {
        return $this->groupables()
            ->where('groupable_id', $model->getKey())
            ->where('groupable_type', $model->getMorphClass())
            ->first();
    }

    public function hasGroupable(Model $model): bool
    {
        return $this->groupables()
            ->where('groupable_id', $model->getKey())
            ->where('groupable_type', $model->getMorphClass())
            ->exists();
    }

    public function isCustom(): bool
    {
        return $this->type === GroupType::Custom;
    }

    public function isMixer(): bool
    {
        return $this->type === GroupType::Mixer;
    }

    public function buildSortQuery(): Builder
    {
        return static::query()->where('user_id', $this->user_id);
    }

    public function prunable(): Builder
    {
        return static::query()
            ->mixer()
            ->where('created_at', '<=', now()->subDay());
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(string $event): array
    {
        return array_filter([$this, $this->user]);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'user_id' => (string) $this->user_id,
            'name' => (string) $this->name,
            'content' => (string) $this->content,
            'groupables' => (int) $this->groupables_count,
            'type' => (string) $this->type?->value ?? '',
            'type_priority' => (int) $this->priority,
            'state' => (string) $this->state,
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
            'deleted_at' => (int) $this->deleted_at?->getTimestamp(),
        ];
    }

    public function makeSearchableUsing(GroupCollection $models): GroupCollection
    {
        return $models->loadCount('groupables');
    }

    protected function makeAllSearchableUsing(GroupQueryBuilder $query): GroupQueryBuilder
    {
        return $query->withCount('groupables');
    }

    public function loadForResource(): static
    {
        return $this->loadCount('groupables');
    }

    /**
     * @return Attribute<string, never>
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name ?: $this->type->label(),
        )->shouldCache();
    }

    /**
     * @return Attribute<int, never>
     */
    protected function priority(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type?->priority() ?? GroupType::Custom->priority(),
        )->shouldCache();
    }
}
