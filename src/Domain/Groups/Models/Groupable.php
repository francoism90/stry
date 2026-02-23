<?php

declare(strict_types=1);

namespace Domain\Groups\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
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
     * @var array<int, string>
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

    public function groupable(): MorphTo
    {
        return $this->MorphTo();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function toSearchableArray(): array
    {
        // Build initial array with common fields
        $array = [
            'id' => (string) $this->getScoutKey(),
            'group_id' => (string) $this->group_id,
            'order_column' => (int) $this->order_column,
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];

        // Add polymorphic fields if the relationship exists
        if ($this->groupable?->exists()) {
            $array["{$this->groupable_type}_id"] = (string) $this->groupable_id;
        }

        return $array;
    }

    public function makeSearchableUsing(Collection $models): Collection
    {
        return $models->loadMissing('groupable');
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('groupable');
    }
}
