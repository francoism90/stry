<?php

declare(strict_types=1);

namespace Domain\Relates\Concerns;

use ArrayAccess;
use Domain\Relates\Models\Related;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    public function syncRelated(array|ArrayAccess|Collection $related = []): static
    {
        $items = $this->convertToRelated($related);

        // Detach models that are not in the new list
        $this
            ->getRelates()
            ->filter(fn (Model $model) => ! $items->contains(fn (array $item) => $item['model_type'] === $model->getMorphClass() &&
                $item['model_id'] === $model->getKey(),
            ))
            ->each(fn (Model $model) => $this->detachRelated($model));

        // Attach new models
        $items->each(fn (array $values) => Related::firstOrCreate($values));

        return $this;
    }

    public function attachRelated(Model $model): Related
    {
        $related = $this->convertToRelated([$model])->first();

        return Related::firstOrCreate($related);
    }

    public function detachRelated(Model $model): bool
    {
        $related = $this->convertToRelated([$model])->first();

        return Related::firstWhere($related)?->delete();
    }

    public function getRelates(): Collection
    {
        return $this
            ->loadMissing('related')
            ->related
            ->groupBy(fn (Related $related) => $this->getActualClassNameForMorph($related->model_type))
            ->flatMap(fn (Collection $typeGroup, string $type) => $type::whereIn('id', $typeGroup->pluck('model_id'))->get());
    }

    public function convertToRelated(array|ArrayAccess|Collection $models = []): Collection
    {
        return collect($models)
            ->filter(fn (mixed $model) => $model instanceof Model)
            ->map(fn (Model $model) => [
                'relatable_type' => $this->getMorphClass(),
                'relatable_id' => $this->getKey(),
                'model_type' => $model->getMorphClass(),
                'model_id' => $model->getKey(),
            ]);
    }

    protected function relates(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getRelates(),
        )->shouldCache();
    }
}
