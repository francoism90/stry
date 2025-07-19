<?php

declare(strict_types=1);

namespace Domain\Tags\Concerns;

use Domain\Tags\Collections\TagCollection;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Tags\HasTags as BaseHasTags;

trait HasTags
{
    use BaseHasTags;

    protected function tagsTranslated(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getTagsByType()
        )->shouldCache();
    }

    protected function tagsRelated(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getRelatedTags()
        )->shouldCache();
    }

    public function getTagsByType(): string
    {
        return TagCollection::make(TagType::cases())
            ->map(fn (TagType $type) => $this->tags()->type($type)->get()->translated())
            ->filter()
            ->implode(', ');
    }

    public function getRelatedTags(): string
    {
        $items = $this->tags
            ->loadMissing('relatables')
            ->flatMap(fn (Tag $tag) => $tag->related)
            ->unique()
            ->all();

        return TagCollection::make($items)->translated();
    }
}
