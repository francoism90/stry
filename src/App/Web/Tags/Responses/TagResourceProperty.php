<?php

declare(strict_types=1);

namespace App\Web\Tags\Responses;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class TagResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Tag $tag = null,
        protected ?array $appends = null,
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

        return $this->tag
            ->loadMissing('related')
            ->loadCount('videos')
            ->append($this->appends ?? [])
            ->toResource(TagResource::class);
    }
}
