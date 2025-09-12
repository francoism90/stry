<?php

declare(strict_types=1);

namespace Domain\Tags\Actions;

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class SetTagsOrder
{
    public function handle(): void
    {
        DB::transaction(function () {
            $items = collect();

            foreach (TagType::cases() as $type) {
                $items = $items->merge($this->getTags($type));
            }

            Tag::setNewOrder($items->pluck('id')->all());
        });
    }

    protected function getTags(TagType $type): LazyCollection
    {
        return Tag::query()
            ->type($type)
            ->cursor()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
    }
}
