<?php

declare(strict_types=1);

namespace App\Client\Tags\Responses;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class TagResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected Tag|string|null $tag = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?TagResource => $this->getResource());
    }

    protected function getResource(): ?TagResource
    {
        if (! $this->tag) {
            return null;
        }

        $tag = Tag::findFromUlid($this->tag);

        return $tag
            ->loadCount('videos')
            ->toResource(TagResource::class);
    }
}
