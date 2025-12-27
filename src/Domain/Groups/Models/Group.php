<?php

declare(strict_types=1);

namespace Domain\Groups\Models;

use Database\Factories\GroupFactory;
use Domain\Groups\Collections\GroupCollection;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\QueryBuilders\GroupQueryBuilder;
use Domain\Groups\States\GroupState;
use Domain\Media\Concerns\InteractsWithMedia;
use Domain\Users\Concerns\InteractsWithUser;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;

class Group extends Model implements HasMedia, Sortable
{
    use BroadcastsEvents;
    use HasFactory;
    use HasStates;
    use HasUlids;
    use InteractsWithMedia;
    use InteractsWithUser;
    use Notifiable;
    use Prunable;
    use Searchable;
    use SoftDeletes;
    use SortableTrait;

    /**
     * @var array<int, string>
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
     * @var array<int, string>
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

    protected static function newFactory(): GroupFactory
    {
        return GroupFactory::new();
    }

    protected function casts(): array
    {
        return [
            'state' => GroupState::class,
            'type' => GroupType::class,
            'options' => AsArrayObject::class,
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function newEloquentBuilder($query): GroupQueryBuilder
    {
        return new GroupQueryBuilder($query);
    }

    public function newCollection(array $models = []): GroupCollection
    {
        return new GroupCollection($models);
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function groupables()
    {
        return $this->hasMany(Groupable::class, 'group_id');
    }

    public function videos(): MorphToMany
    {
        return $this
            ->morphedByMany(Video::class, 'groupable')
            ->using(Groupable::class)
            ->withPivot(['group_id', 'options'])
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
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(string $event): array
    {
        return array_filter([$this, $this->user]);
    }

    public function broadcastChannel(): string
    {
        return 'groups.'.$this->getRouteKey();
    }

    public function broadcastChannelRoute(): string
    {
        return 'groups.{group}';
    }

    public function broadcastAs(string $event): string
    {
        return "group.{$event}";
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
            'user_id' => (int) $this->user_id,
            'name' => (string) $this->name,
            'content' => (string) $this->content,
            'type' => (string) $this->type?->value ?? '',
            'state' => (string) $this->state,
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::of($this->name ?: $this->kind)->apa(),
        )->shouldCache();
    }
}
