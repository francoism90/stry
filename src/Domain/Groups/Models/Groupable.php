<?php

declare(strict_types=1);

namespace Domain\Groups\Models;

use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Scout\Searchable;

class Groupable extends MorphPivot
{
    use Searchable;

    /**
     * @var string
     */
    protected $table = 'groupables';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'group_id',
        'groupable_id',
        'groupable_type',
        'options',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'options' => AsArrayObject::class,
        ];
    }

    public function newQueryForRestoration($ids): Builder
    {
        return is_array($ids)
            ? $this->newQueryWithoutScopes()->whereIn($this->getQualifiedKeyName(), $ids)
            : $this->newQueryWithoutScopes()->whereKey($ids);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function groupable(): MorphTo
    {
        return $this->MorphTo();
    }

    /**
     * @return BelongsTo<Group, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function shouldBeSearchable(): bool
    {
        return $this->groupable !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $groupableType = (string) $this->groupable_type;
        $groupableId = (string) $this->groupable_id;

        return [
            'id' => (string) $this->getScoutKey(),
            'group_id' => (string) $this->group_id,
            'groupable_id' => $groupableId,
            'groupable_type' => $groupableType,
            "{$groupableType}_id" => $groupableId,
            'order_column' => (int) $this->order_column,
            'progress' => $this->watchedFraction(),
            'created_at' => (int) $this->created_at?->getTimestamp(),
            'updated_at' => (int) $this->updated_at?->getTimestamp(),
        ];
    }

    /**
     * Share of a video watched so far, for memberships that store a watch position (the viewed group).
     */
    protected function watchedFraction(): ?float
    {
        $time = data_get($this->options ?? [], 'time');

        if (! is_numeric($time) || ! $this->groupable instanceof Video) {
            return null;
        }

        $duration = (float) $this->groupable->duration;

        if ($duration <= 0) {
            return null;
        }

        return round(min((float) $time / $duration, 1), 4);
    }

    /**
     * @param  Collection<int, self>  $models
     * @return Collection<int, self>
     */
    public function makeSearchableUsing(Collection $models): Collection
    {
        return $models->loadMissing('groupable');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('groupable');
    }
}
