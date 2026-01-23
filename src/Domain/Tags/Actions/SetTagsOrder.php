<?php

declare(strict_types=1);

namespace Domain\Tags\Actions;

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Support\Collection;

class SetTagsOrder
{
    public function handle(): void
    {
        $items = collect();

        foreach (TagType::cases() as $type) {
            $items = $items->merge($this->getCollection($type));
        }

        Tag::setNewOrder($items->pluck('id')->all());
    }

    protected function getCollection(TagType $type): Collection
    {
        return Tag::query()
            ->type($type)
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
    }
}
