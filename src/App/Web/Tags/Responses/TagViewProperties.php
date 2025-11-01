<?php

declare(strict_types=1);

namespace App\Web\Tags\Responses;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Illuminate\Container\Attributes\RouteParameter;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

readonly class TagViewProperties implements ProvidesInertiaProperties
{
    public function __construct(
        #[RouteParameter('tag')] protected Tag $tag,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'tag' => fn () => $this->tag->loadCount('videos')->toResource(TagResource::class),
        ];
    }
}
