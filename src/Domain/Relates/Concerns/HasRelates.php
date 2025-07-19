<?php

declare(strict_types=1);

namespace Domain\Relates\Concerns;

use Domain\Relates\Models\Relatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasRelates
{
    public function relatables(): MorphMany
    {
        return $this->morphMany(Relatable::class, 'model')->chaperone();
    }

    public function loadRelated(): Collection
    {
        return $this->relatables
            ->groupBy(function (Relatable $relatable) {
                return $this->getActualClassNameForMorph($relatable->relate_type);
            })
            ->flatMap(function (Collection $typeGroup, string $type) {
                return $type::whereIn('id', $typeGroup->pluck('relate_id'))->get();
            });
    }

    public function hasRelated(): bool
    {
        return ! $this->related->isEmpty();
    }

    public function relate(Model|int $item, ?string $type = null): Relatable
    {
        return Relatable::firstOrCreate(
            $this->getRelatableValues($item, $type)
        );
    }

    public function unrelate(Model|int $item, ?string $type = null): int
    {
        return Relatable::where($this->getRelatableValues($item, $type))->delete();
    }

    public function syncRelated(Collection|array $items = [], bool $detaching = true): void
    {
        $items = $this->getSyncRelatedValues($items);

        $current = $this->relatables->map(function (Relatable $relatable) {
            return $relatable->getRelatedValues();
        });

        $items->each(function (array $values) {
            $this->relate($values['id'], $values['type']);
        });

        if (! $detaching) {
            return;
        }

        $current
            ->filter(function (array $values) use ($items) {
                return ! $items->contains($values);
            })
            ->each(function (array $values) {
                $this->unrelate($values['id'], $values['type']);
            });
    }

    protected function getSyncRelatedValues(Collection|array $items): Collection
    {
        if ($items instanceof Collection) {
            return $items->map(function (Model $item): array {
                return [
                    'type' => $item->getMorphClass(),
                    'id' => $item->getKey(),
                ];
            });
        }

        return collect($items);
    }

    protected function getRelatableValues(Model|int $item, ?string $type = null): array
    {
        if (! $item instanceof Model && blank($type)) {
            throw new \InvalidArgumentException(
                'If an id is specified as an item, the type isn\'t allowed to be empty.'
            );
        }

        return [
            'model_id' => $this->getKey(),
            'model_type' => $this->getMorphClass(),
            'relate_id' => $item instanceof Model ? $item->getKey() : $item,
            'relate_type' => $item instanceof Model ? $item->getMorphClass() : $type,
        ];
    }

    protected function related(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->loadRelated()
        )->shouldCache();
    }
}
