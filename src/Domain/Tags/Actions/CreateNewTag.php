<?php

declare(strict_types=1);

namespace Domain\Tags\Actions;

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Support\Facades\DB;

class CreateNewTag
{
    public function execute(array $attributes): Tag
    {
        return DB::transaction(function () use ($attributes) {
            $attributes['type'] = TagType::from($attributes['type'])?->value;

            $model = Tag::findOrCreate(
                $attributes['name'],
                $attributes['type'],
            );

            return $model;
        });
    }
}
