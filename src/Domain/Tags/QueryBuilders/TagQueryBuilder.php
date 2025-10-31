<?php

declare(strict_types=1);

namespace Domain\Tags\QueryBuilders;

use ArrayAccess;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TagQueryBuilder extends Builder
{
    public function whereUlid(Tag|ArrayAccess|string|null $values = null): self
    {
        $tags = Collection::make((array) $values)
            ->map(fn (Tag|string $tag) => $tag instanceof Tag ? $tag->getKey() : Tag::firstWhere('ulid', $tag))
            ->values()
            ->toArray();

        return $this->whereIn('id', $tags);
    }

    public function type(TagType|string $value): self
    {
        $type = $value instanceof TagType ? $value : TagType::from($value);

        return $this->where('type', $type);
    }
}
