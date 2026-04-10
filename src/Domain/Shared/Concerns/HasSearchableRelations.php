<?php

declare(strict_types=1);

namespace Domain\Shared\Concerns;

use Illuminate\Database\Eloquent\Model;
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
        static::saved(function (Model $model) {
            if (! $model->wasChanged()) {
                return;
            }

            $class = static::class;

            if (array_key_exists($class, static::$syncing)) {
                return;
            }

            static::$syncing[$class] = true;

            try {
                foreach ($model->searchableRelations() as $relation) {
                    $related = $model->{$relation}()->getRelated();

                    if (! in_array(Searchable::class, class_uses_recursive($related))) {
                        continue;
                    }

                    $model->{$relation}()->lazyById()->each->searchable();
                }
            } finally {
                unset(static::$syncing[$class]);
            }
        });
    }

    /**
     * Return the relationship names whose models should be re-indexed
     * after this model is saved.
     *
     * @return array<int, string>
     */
    public function searchableRelations(): array
    {
        return [];
    }
}
