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
            $tag->updateOrFail(
                Arr::only($attributes, $tag->getFillable())
            );

            if (array_key_exists('related', $attributes)) {
                $tag->syncRelated(Tag::fromOption($attributes['related'] ?? [])->get());
            }

            return $tag;
        });
    }
}
