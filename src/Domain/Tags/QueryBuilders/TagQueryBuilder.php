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
    public function options(Tag|ArrayAccess|array|string $values): self
    {
        if ($values instanceof Tag) {
            return $this->where('id', $values->getKey());
        }

        // Convert values to a collection of Tag IDs
        $values = Collection::make((array) $values)
            ->map(fn (Tag|string $tag) => $tag instanceof Tag ? $tag : Tag::firstWhere('ulid', $tag))
            ->pluck('id')
            ->filter();

        return $this->whereIn('id', $values);
    }

    public function type(TagType|string $value): self
    {
        $type = $value instanceof TagType ? $value : TagType::from($value);

        return $this->where('type', $type);
    }
}
