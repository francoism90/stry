<?php

declare(strict_types=1);

namespace Domain\Tags\QueryBuilders;

use Domain\Tags\Enums\TagType;
use Illuminate\Database\Eloquent\Builder;

class TagQueryBuilder extends Builder
{
    public function type(TagType|string $value): self
    {
        $type = $value instanceof TagType ? $value : TagType::from($value);

        return $this->where('type', $type);
    }
}
