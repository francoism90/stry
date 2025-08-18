<?php

declare(strict_types=1);

namespace Domain\Relates\Concerns;

use ArrayAccess;
use Domain\Relates\Models\Related;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

trait InteractsWithRelated
{
    public static function bootInteractsWithRelated(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (! $model->forceDeleting) {
                    return;
                }
            }

            $model->related()->cursor()->each(fn (Related $related) => $related->delete());
        });
    }

    public function related(): MorphMany
    {
        return $this->morphMany(Related::class, 'relatable')->chaperone();
    }

    public function relates(): Collection
    {
        return $this->related
            ->groupBy(fn (Related $related) => $this->getActualClassNameForMorph($related->relatable_type))
            ->flatMap(fn (Collection $typeGroup, string $type) => $type::whereIn('id', $typeGroup->pluck('relatable_id'))->get());
    }

    public function relate(Model $model, array $attributes = []): Related
    {
        return Related::firstOrCreate([
            'relatable_type' => $this->getMorphClass(),
            'relatable_id' => $this->getKey(),
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ], ...$attributes);
    }

    public function syncRelated(array|ArrayAccess|Collection $related = []): static
    {
        $related = static::convertToRelated($related);

        $related->each(fn (Model $model) => $this->relate($model));

        return $this;
    }

    public static function convertToRelated(array|ArrayAccess|Collection $values = []): Collection
    {
        return collect($values)
            ->filter(fn (mixed $value) => $value instanceof Model);
    }
}
