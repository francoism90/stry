<?php

declare(strict_types=1);

namespace Domain\Shared\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\Searchable;

trait HasSearchableRelations
{
    /**
     * Tracks which model classes are currently mid-cascade to prevent re-entry.
     *
     * @var array<class-string, bool>
     */
    private static array $syncing = [];

    public static function bootHasSearchableRelations(): void
    {
        static::saved(function (Model $model): void {
            if ($model->wasChanged()) {
                $model->reindexSearchableRelations();
            }
        });

        static::deleted(fn (Model $model) => $model->reindexSearchableRelations());
    }

    protected function reindexSearchableRelations(): void
    {
        $class = static::class;

        if (array_key_exists($class, static::$syncing)) {
            return;
        }

        static::$syncing[$class] = true;

        try {
            foreach ($this->searchableRelations() as $relation) {
                $this->reindexSearchableRelation($relation);
            }
        } finally {
            unset(static::$syncing[$class]);
        }
    }

    /**
     * Return the relationship names whose models should be re-indexed
     * after this model is saved or deleted.
     *
     * @return array<int, string>
     */
    public function searchableRelations(): array
    {
        return [];
    }

    /**
     * Re-index all related models for the given relationship name.
     *
     * Applies makeAllSearchableUsing (if defined) to the relation query before
     * streaming, mirroring Scout's bulk import behaviour and preventing N+1
     * queries when toSearchableArray() accesses eager-loaded relationships.
     */
    protected function reindexSearchableRelation(string $relation): void
    {
        $query = $this->{$relation}();
        $related = $query->getRelated();

        if (! in_array(Searchable::class, class_uses_recursive($related))) {
            return;
        }

        if (method_exists($related, 'makeAllSearchableUsing')) {
            Closure::bind(
                fn ($q) => $this->makeAllSearchableUsing($q),
                $related,
                get_class($related),
            )($query->getQuery());
        }

        $query->chunkById(Config::integer('scout.chunk.searchable', 500), fn ($chunk) => $chunk->searchable());
    }
}
