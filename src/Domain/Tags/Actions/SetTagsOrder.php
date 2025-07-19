<?php

declare(strict_types=1);

namespace Domain\Tags\Actions;

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Support\LazyCollection;

class SetTagsOrder
{
    public function execute(): void
    {
        $items = collect();

        foreach (TagType::cases() as $type) {
            $items = $items->merge($this->getTags($type));
        }

        Tag::setNewOrder($items->pluck('id')->all());
    }

    protected function getTags(TagType $value): LazyCollection
    {
        return Tag::query()
            ->type($value)
            ->cursor()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
    }
}
