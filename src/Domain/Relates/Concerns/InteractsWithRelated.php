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
        $items = $this
            ->convertToRelated($related)
            ->mapWithKeys(fn (array $values): array => [
                $this->relatedKey($values['model_type'], $values['model_id']) => $values,
            ]);

        $this
            ->related()
            ->cursor()
            ->filter(fn (Related $relation): bool => ! $items->has(
                $this->relatedKey($relation->model_type, $relation->model_id),
            ))
            ->each(function (Related $relation): void {
                $relation->delete();
            });

        $items->each(fn (array $values): Related => Related::firstOrCreate($values));

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
            ->map(function (mixed $model): ?array {
                if (! $model instanceof Model) {
                    return null;
                }

                return [
                    'relatable_type' => $this->getMorphClass(),
                    'relatable_id' => $this->getKey(),
                    'model_type' => $model->getMorphClass(),
                    'model_id' => $model->getKey(),
                ];
            })
            ->filter()
            ->values();
    }

    protected function relatedKey(string $type, mixed $id): string
    {
        return sprintf('%s::%s', $type, (string) $id);
    }

    protected function relates(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getRelates(),
        )->shouldCache();
    }
}
