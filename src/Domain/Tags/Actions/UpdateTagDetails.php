<?php

declare(strict_types=1);

namespace Domain\Tags\Actions;

use Domain\Tags\Models\Tag;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateTagDetails
{
    public function handle(Tag $tag, array $attributes = []): mixed
    {
        return DB::transaction(function () use ($tag, $attributes) {
            // Update the tag attributes
            $tag->updateOrFail(
                Arr::only($attributes, $tag->getFillable())
            );

            // Sync related tags if provided
            if (array_key_exists('related', $attributes)) {
                $tagIds = Tag::hasUlid(data_get($attributes, 'related.*.id', []))->get();

                $tag->syncRelated($tagIds);
            }
        });
    }
}
