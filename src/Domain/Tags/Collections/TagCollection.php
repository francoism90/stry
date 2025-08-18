<?php

declare(strict_types=1);

namespace Domain\Tags\Collections;

use Domain\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagCollection extends Collection
{
    public function relates(): mixed
    {
        return $this
            ->flatMap(fn (Tag $item) => $item->getRelates())
            ->unique('id');
    }

    public function synonyms(): mixed
    {
        return $this
            ->relates()
            ->map(fn (Tag $related) => $related->only(['name', 'description']))
            ->flatten()
            ->unique()
            ->filter()
            ->implode(', ');
    }

    public function translated(): mixed
    {
        return $this->map(fn (Tag $item) => [
            'name' => $item->getTranslations('name'),
            'description' => $item->getTranslations('description'),
        ])
            ->flatten()
            ->unique()
            ->filter()
            ->implode(', ');
    }
}
