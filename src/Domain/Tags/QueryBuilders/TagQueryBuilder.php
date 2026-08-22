<?php

declare(strict_types=1);

namespace Domain\Tags\QueryBuilders;

use ArrayAccess;
use Domain\Profiles\Models\Profile;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Builder;

class TagQueryBuilder extends Builder
{
    public function options(Tag|ArrayAccess|array|string $values): self
    {
        return $this->whereIn('id', Tag::resolveTagIds($values));
    }

    public function type(TagType|string $value): self
    {
        $type = $value instanceof TagType ? $value : TagType::from($value);

        return $this->where('type', $type);
    }

    public function forProfile(?Profile $profile = null): self
    {
        if ($profile?->isKids()) {
            return $this->where('adult', false);
        }

        return $this;
    }
}
