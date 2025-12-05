<?php

declare(strict_types=1);

namespace App\Admin\Tags\Responses;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Illuminate\Container\Attributes\RouteParameter;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class TagResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        #[RouteParameter('tag')] protected Tag $tag,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): TagResource => $this->getResource());
    }

    protected function getResource(): TagResource
    {
        return $this->tag
            ->append('description', 'type', 'relates')
            ->toResource(TagResource::class);
    }
}
