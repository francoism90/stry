<?php

declare(strict_types=1);

namespace Domain\Tags\Collections;

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagCollection extends Collection
{
    public function routeKeys(): mixed
    {
        return $this->pluck((new Tag)->getRouteKeyName());
    }

    public function type(TagType|string|null $type = null): mixed
    {
        if (is_string($type)) {
            $type = TagType::tryFrom($type);
        }

        return $this->filter(
            fn (Tag $item) => $item->type === $type->value
        );
    }

    public function translations(): mixed
    {
        return $this->flatMap(fn (Tag $item) => collect($item->getTranslations())
            ->only(['name', 'description'])
            ->flatten()
            ->filter()
            ->unique()
            ->all()
        );
    }

    public function translated(): mixed
    {
        return $this
            ->translations()
            ->flatten()
            ->filter()
            ->unique()
            ->implode(', ');
    }
}
