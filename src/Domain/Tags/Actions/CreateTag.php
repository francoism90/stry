<?php

declare(strict_types=1);

namespace Domain\Tags\Actions;

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Support\Facades\DB;

class CreateTag
{
    public function __construct(protected SetTagsOrder $sorter) {}

    public function handle(string $name, TagType $type, ?string $description = null, ?string $locale = null): Tag
    {
        return DB::transaction(function () use ($name, $type, $description, $locale) {
            /** @var Tag $tag */
            $tag = Tag::findOrCreate($name, $type->value, $locale ?? app()->getLocale());

            if (filled($description)) {
                $tag->description = $description;
                $tag->saveOrFail();
            }

            // Keep tags in their natural display order
            $this->sorter->handle();

            return $tag;
        });
    }
}
