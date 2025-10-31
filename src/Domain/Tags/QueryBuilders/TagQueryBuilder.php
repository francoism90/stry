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
    public function hasUlid(Tag|ArrayAccess|array|string|null $values = null): self
    {
        $values = Collection::make((array) $values)
            ->map(fn (Tag|string $tag) => $tag instanceof Tag ? $tag->getKey() : Tag::firstWhere('ulid', $tag)->getKey());

        return $this->whereIn('id', $values->filter());
    }

    public function type(TagType|string $value): self
    {
        $type = $value instanceof TagType ? $value : TagType::from($value);

        return $this->where('type', $type);
    }
}
